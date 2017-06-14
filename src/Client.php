<?php

namespace Vault;

use Cache\Adapter\Common\CacheItem;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Request;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Vault\AuthenticationStrategies\AuthenticationStrategy;
use Vault\Builders\ResponseBuilder;
use Vault\Exceptions\ClientException;
use Vault\Exceptions\DependencyException;
use Vault\Exceptions\ServerException;
use Vault\Helpers\ModelHelper;
use Vault\Models\Token;
use Vault\ResponseModels\Response;
use Vault\Transports\Transport;

/**
 * Class Client
 *
 * @package Vault
 */
class Client implements LoggerAwareInterface
{
    const VERSION_1 = 'v1';

    const TOKEN_CACHE_KEY = 'token';

    use LoggerAwareTrait;

    /**
     * @var string
     */
    protected $version = self::VERSION_1;

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
     * @param Transport       $transport
     * @param LoggerInterface $logger
     */
    public function __construct(Transport $transport, LoggerInterface $logger = null)
    {
        $this->transport = $transport;
        $this->logger = $logger ?: new NullLogger();
        $this->responseBuilder = new ResponseBuilder();
    }

    /**
     * @return bool
     *
     * @throws \Vault\Exceptions\TransportException
     * @throws \Vault\Exceptions\DependencyException
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Vault\Exceptions\ServerException
     * @throws \Vault\Exceptions\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function authenticate()
    {
        if ($this->token = $this->getTokenFromCache()) {
            $this->logger->debug('Using token from cache.');

            $this->writeTokenInfoToDebugLog();

            return (bool)$this->token;
        }

        if (!$this->authenticationStrategy) {
            $this->logger->critical('Trying to authenticate without strategy.');

            throw new DependencyException(sprintf(
                'Specify authentication strategy before calling this method (%s).',
                __METHOD__
            ));
        }

        $this->logger->debug('Trying to authenticate.');

        if ($auth = $this->authenticationStrategy->authenticate()) {
            $this->logger->debug('Authentication was successful.', ['clientToken' => $auth->getClientToken()]);

            // temporary
            $this->token = new Token(['auth' => $auth]);

            // get info about self
            $response = $this->get('/v1/auth/token/lookup-self');

            $this->token = new Token(array_merge(ModelHelper::camelize($response->getData()), ['auth' => $auth]));

            $this->writeTokenInfoToDebugLog();

            $this->putTokenIntoCache();

            return true;
        }

        return false;
    }

    /**
     * @TODO: move to separated class
     *
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

        if (!$token || !$token->getAuth()) {
            $this->logger->debug('No token in cache or auth is empty, returning null.');

            return null;
        }

        // invalidate token
        if (time() > $token->getCreationTime() + $token->getCreationTtl()) {
            $this->logger->debug('Token is expired.');

            $this->writeTokenInfoToDebugLog();

            return null;
        }

        return $token;
    }

    private function writeTokenInfoToDebugLog()
    {
        $this->logger->debug('Token info.', [
            'clientToken' => $this->token->getAuth()->getClientToken(),
            'id' => $this->token->getId(),
            'creationTime' => $this->token->getCreationTime(),
            'ttl' => $this->token->getCreationTtl(),
        ]);
    }

    /**
     * @param string $url
     * @param array  $options
     *
     * @return Response
     *
     * @throws \Vault\Exceptions\TransportException
     * @throws \Vault\Exceptions\ServerException
     * @throws \Vault\Exceptions\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function get($url = null, array $options = [])
    {
        return $this->responseBuilder->build($this->send(new Request('GET', $url), $options));
    }

    /**
     * @param RequestInterface $request
     * @param array            $options
     *
     * @return ResponseInterface
     *
     * @throws \Vault\Exceptions\TransportException
     * @throws \Vault\Exceptions\ClientException
     * @throws \Vault\Exceptions\ServerException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function send(RequestInterface $request, array $options = [])
    {
        $request = $request->withHeader('User-Agent', 'VaultPHP/1.0.0');
        $request = $request->withHeader('Content-Type', 'application/json');

        if ($this->token) {
            $request = $request->withHeader('X-Vault-Token', $this->token->getAuth()->getClientToken());
        }

        $this->logger->debug('Request.', [
            'method' => $request->getMethod(),
            'uri' => $request->getUri(),
            'headers' => $request->getHeaders(),
            'body' => $request->getBody()->getContents(),
        ]);

        try {
            $response = $this->transport->send($request, $options);
        } catch (TransferException $e) {
            $this->logger->error('Something went wrong when calling Vault.', [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);

            $this->logger->debug('Trace.', ['exception' => $e]);

            throw new ServerException(sprintf('Something went wrong when calling Vault (%s).', $e->getMessage()));
        }

        $this->logger->debug('Response.', [
            'statusCode' => $response->getStatusCode(),
            'reasonPhrase' => $response->getReasonPhrase(),
            'headers ' => $response->getHeaders(),
            'body' => $response->getBody()->getContents(),
        ]);

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
                "Something went wrong when calling Vault (%s - %s)\n%s.",
                $response->getStatusCode(),
                $response->getReasonPhrase(),
                $response->getBody()->getContents()
            );

            if ($response->getStatusCode() >= 500) {
                throw new ServerException($message, $response->getStatusCode(), $response);
            }

            throw new ClientException($message, $response->getStatusCode(), $response);
        }
    }

    /**
     * @TODO: move to separated class
     *
     * @return bool
     */
    protected function putTokenIntoCache()
    {
        if (!$this->cache) {
            return true; // just ignore
        }

        $authItem = (new CacheItem(self::TOKEN_CACHE_KEY))
            ->set($this->token)
            ->expiresAfter($this->token->getAuth()->getLeaseDuration());

        $this->logger->debug('Token is saved into cache.');

        return $this->cache->save($authItem);
    }

    /**
     * @param string $url
     * @param array  $options
     *
     * @return Response
     *
     * @throws \Vault\Exceptions\TransportException
     * @throws \Vault\Exceptions\ServerException
     * @throws \Vault\Exceptions\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function head($url, array $options = [])
    {
        return $this->responseBuilder->build($this->send(new Request('HEAD', $url), $options));
    }

    /**
     * @param string $url
     * @param array  $options
     *
     * @return Response
     *
     * @throws \Vault\Exceptions\TransportException
     * @throws \Vault\Exceptions\ServerException
     * @throws \Vault\Exceptions\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function put($url, array $options = [])
    {
        return $this->responseBuilder->build($this->send(new Request('PUT', $url), $options));
    }

    /**
     * @param string $url
     * @param array  $options
     *
     * @return Response
     *
     * @throws \Vault\Exceptions\TransportException
     * @throws \Vault\Exceptions\ServerException
     * @throws \Vault\Exceptions\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function patch($url, array $options = [])
    {
        return $this->responseBuilder->build($this->send(new Request('PATCH', $url), $options));
    }

    /**
     * @param string $url
     * @param array  $options
     *
     * @return Response
     *
     * @throws \Vault\Exceptions\TransportException
     * @throws \Vault\Exceptions\ServerException
     * @throws \Vault\Exceptions\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function options($url, array $options = [])
    {
        return $this->responseBuilder->build($this->send(new Request('OPTIONS', $url), $options));
    }

    /**
     * @param string $path
     *
     * @return Response
     *
     * @throws \Vault\Exceptions\TransportException
     * @throws \Vault\Exceptions\ServerException
     * @throws \Vault\Exceptions\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function read($path)
    {
        return $this->get($this->buildPath($path));
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function buildPath($path)
    {
        if (!$this->version) {
            $this->logger->warning('API version is not set!');

            return $path;
        }

        return sprintf('/%s%s', $this->version, $path);
    }

    /**
     * @param string $path
     * @param array  $data
     *
     * @return Response
     *
     * @throws \Vault\Exceptions\TransportException
     * @throws \Vault\Exceptions\ServerException
     * @throws \Vault\Exceptions\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function write($path, array $data = [])
    {
        return $this->post($this->buildPath($path), ['body' => json_encode($data)]);
    }

    /**
     * @param string $url
     * @param array  $options
     *
     * @return Response
     *
     * @throws \Vault\Exceptions\TransportException
     * @throws \Vault\Exceptions\ServerException
     * @throws \Vault\Exceptions\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function post($url, array $options = [])
    {
        return $this->responseBuilder->build($this->send(new Request('POST', $url), $options));
    }

    /**
     * @param string $path
     *
     * @return Response
     *
     * @throws \Vault\Exceptions\TransportException
     * @throws \Vault\Exceptions\ServerException
     * @throws \Vault\Exceptions\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function revoke($path)
    {
        return $this->delete($this->buildPath($path));
    }

    /**
     * @param string $url
     * @param array  $options
     *
     * @return Response
     *
     * @throws \Vault\Exceptions\TransportException
     * @throws \Vault\Exceptions\ServerException
     * @throws \Vault\Exceptions\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function delete($url, array $options = [])
    {
        return $this->responseBuilder->build($this->send(new Request('DELETE', $url), $options));
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     *
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
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