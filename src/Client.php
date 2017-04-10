<?php

namespace Vault;

use Cache\Adapter\Common\CacheItem;
use GuzzleHttp\ClientInterface as Transport;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Vault\AuthenticationStrategies\AuthenticationStrategy;
use Vault\Builders\ResponseBuilder;
use Vault\Exceptions\ClientException;
use Vault\Exceptions\DependencyException;
use Vault\Exceptions\ServerException;
use Vault\Models\Token;
use Vault\ResponseModels\Response;

/**
 * Class Client
 *
 * @package Vault
 */
class Client implements LoggerAwareInterface
{
    const TOKEN_CACHE_KEY = 'token';

    use LoggerAwareTrait;

    /**
     * @var Token
     */
    protected $token;

    /**
     * @var Transport
     */
    protected $transport;

    /**
     * @var CacheItemPoolInterface
     */
    protected $cache;

    /**
     * @var AuthenticationStrategy
     */
    protected $authenticationStrategy;

    /**
     * @var ResponseBuilder
     */
    protected $responseBuilder;

    /**
     * Client constructor.
     *
     * @param array           $options
     * @param LoggerInterface $logger
     * @param Transport       $transport
     */
    public function __construct(array $options = [], LoggerInterface $logger = null, Transport $transport = null)
    {
        $options = array_merge([
            'base_url' => 'http://127.0.0.1:8200',
            'defaults' => [
                'exceptions' => false,
                'headers' => [
                    'User-Agent' => 'VaultPHP/1.0.0',
                    'Content-Type' => 'application/json',
                ],
            ],
        ], $options);

        $this->transport = $transport ?: new \GuzzleHttp\Client($options);
        $this->logger = $logger ?: new NullLogger();
        $this->responseBuilder = new ResponseBuilder();
    }

    /**
     * @return bool
     *
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Vault\Exceptions\DependencyException
     */
    public function authenticate()
    {
        if ($this->token = $this->getTokenFromCache()) {
            return (bool)$this->token;
        }

        if (!$this->authenticationStrategy) {
            throw new DependencyException(sprintf(
                'Specify authentication strategy before calling this method (%s).',
                __METHOD__
            ));
        }

        if ($auth = $this->authenticationStrategy->authenticate()) {
            // temporary
            $this->token = new Token(['auth' => $auth]);

            // get info about self
            $response = $this->get('/v1/auth/token/lookup-self');

            $this->token = new Token(array_merge($response->getData(), ['auth' => $auth]));

            $this->putWhoamiIntoCache();

            return true;
        }

        return false;
    }

    /**
     * @return Token|null
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function getTokenFromCache()
    {
        if (!$this->cache || !$this->cache->hasItem(self::TOKEN_CACHE_KEY)) {
            return null;
        }

        /** @var Token $token */
        $token = $this->cache->getItem(self::TOKEN_CACHE_KEY)->get();

        // invalidate token
        if (!$token || time() > $token->getCreationTime() + $token->getCreationTtl()) {
            return null;
        }

        return $token;
    }

    /**
     * @param string $url
     * @param array  $options
     *
     * @return Response
     *
     * @throws \Vault\Exceptions\ServerException
     * @throws \Vault\Exceptions\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function get($url = null, array $options = [])
    {
        return $this->responseBuilder->build($this->send($this->transport->createRequest('GET', $url, $options)));
    }

    /**
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     *
     * @throws \RuntimeException
     */
    public function send(RequestInterface $request)
    {
        if ($this->token) {
            $request->addHeader('X-Vault-Token', $this->token->getAuth()->getClientToken());
        }

        $this->logger->info(sprintf('%s "%s"', $request->getMethod(), $request->getUrl()));
        $this->logger->debug(sprintf(
            "Request: \n%s\n%s\n%s",
            $request->getUrl(),
            $request->getMethod(),
            json_encode($request->getHeaders())
        ));

        try {
            $response = $this->transport->send($request);
        } catch (TransferException $e) {
            $message = sprintf('Something went wrong when calling Vault (%s).', $e->getMessage());

            $this->logger->error($message);

            throw new ServerException($message);
        }

        $this->logger->debug(sprintf("Response: \n%s", (string)$response->getBody()));

        $this->checkResponse($response);

        return $response;
    }

    /**
     * @param ResponseInterface $response
     *
     * @throws \Vault\Exceptions\ClientException
     * @throws \Vault\Exceptions\ServerException
     * @throws \RuntimeException
     */
    protected function checkResponse(ResponseInterface $response)
    {
        if ($response->getStatusCode() >= 400) {
            $message = sprintf(
                'Something went wrong when calling Vault (%s - %s).',
                $response->getStatusCode(),
                $response->getReasonPhrase()
            );

            $this->logger->error($message);
            $this->logger->debug(sprintf(
                "Response: \n%s\n%s\n%s",
                $response->getStatusCode(),
                json_encode($response->getHeaders()),
                $response->getBody()->getContents()
            ));

            $message .= "\n" . (string)$response->getBody();

            if ($response->getStatusCode() >= 500) {
                throw new ServerException($message, $response->getStatusCode(), $response);
            }

            throw new ClientException($message, $response->getStatusCode(), $response);
        }
    }

    /**
     * @return bool
     */
    protected function putWhoamiIntoCache()
    {
        if (!$this->cache) {
            return true; // just ignore
        }

        $authItem = (new CacheItem(self::TOKEN_CACHE_KEY))
            ->set($this->token)
            ->expiresAfter($this->token->getAuth()->getLeaseDuration());

        return $this->cache->save($authItem);
    }

    /**
     * @param string $url
     * @param array  $options
     *
     * @return Response
     *
     * @throws \Vault\Exceptions\ServerException
     * @throws \Vault\Exceptions\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function head($url, array $options = [])
    {
        return $this->responseBuilder->build($this->send($this->transport->createRequest('HEAD', $url, $options)));
    }

    /**
     * @param string $url
     * @param array  $options
     *
     * @return Response
     *
     * @throws \Vault\Exceptions\ServerException
     * @throws \Vault\Exceptions\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function delete($url, array $options = [])
    {
        return $this->responseBuilder->build($this->send($this->transport->createRequest('DELETE', $url, $options)));
    }

    /**
     * @param string $url
     * @param array  $options
     *
     * @return Response
     *
     * @throws \Vault\Exceptions\ServerException
     * @throws \Vault\Exceptions\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function put($url, array $options = [])
    {
        return $this->responseBuilder->build($this->send($this->transport->createRequest('PUT', $url, $options)));
    }

    /**
     * @param string $url
     * @param array  $options
     *
     * @return Response
     *
     * @throws \Vault\Exceptions\ServerException
     * @throws \Vault\Exceptions\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function patch($url, array $options = [])
    {
        return $this->responseBuilder->build($this->send($this->transport->createRequest('PATCH', $url, $options)));
    }

    /**
     * @param string $url
     * @param array  $options
     *
     * @return Response
     *
     * @throws \Vault\Exceptions\ServerException
     * @throws \Vault\Exceptions\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function post($url, array $options = [])
    {
        return $this->responseBuilder->build($this->send($this->transport->createRequest('POST', $url, $options)));
    }

    /**
     * @param string $url
     * @param array  $options
     *
     * @return Response
     *
     * @throws \Vault\Exceptions\ServerException
     * @throws \Vault\Exceptions\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function options($url, array $options = [])
    {
        return $this->responseBuilder->build($this->send($this->transport->createRequest('OPTIONS', $url, $options)));
    }

    /**
     * @return Token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param Token $token
     *
     * @return $this
     */
    public function setToken(Token $token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return Transport
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * @param Transport $transport
     *
     * @return $this
     */
    public function setTransport($transport)
    {
        $this->transport = $transport;

        return $this;
    }

    /**
     * Sets a logger.
     *
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @return CacheItemPoolInterface
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param CacheItemPoolInterface $cache
     *
     * @return $this
     */
    public function setCache($cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * @return AuthenticationStrategy
     */
    public function getAuthenticationStrategy()
    {
        return $this->authenticationStrategy;
    }

    /**
     * @param AuthenticationStrategy $authenticationStrategy
     *
     * @return $this
     */
    public function setAuthenticationStrategy(AuthenticationStrategy $authenticationStrategy)
    {
        $authenticationStrategy->setClient($this);

        $this->authenticationStrategy = $authenticationStrategy;

        return $this;
    }

    /**
     * @return ResponseBuilder
     */
    public function getResponseBuilder()
    {
        return $this->responseBuilder;
    }

    /**
     * @param ResponseBuilder $responseBuilder
     *
     * @return $this
     */
    public function setResponseBuilder(ResponseBuilder $responseBuilder)
    {
        $this->responseBuilder = $responseBuilder;

        return $this;
    }
}