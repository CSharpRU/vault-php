<?php

namespace Vault;

use Cache\Adapter\Common\CacheItem;
use GuzzleHttp\ClientInterface as Transport;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Request;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Vault\AuthenticationStrategy\AuthenticationStrategy;
use Vault\Exception\ClientException;
use Vault\Exception\DependencyException;
use Vault\Exception\ServerException;

/**
 * Class Client
 *
 * @package Vault
 */
class Client implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var string
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
     * @var int
     */
    protected $cacheTtl = 3600;

    /**
     * @var AuthenticationStrategy
     */
    protected $authenticationStrategy;

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
            'base_uri' => 'http://127.0.0.1:8200',
            'http_errors' => false,
            'headers' => [
                'User-Agent' => 'VaultPHP/1.0.0',
                'Content-Type' => 'application/json',
            ],
        ], $options);

        $this->transport = $transport ?: new \GuzzleHttp\Client($options);
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * @return bool
     *
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Vault\Exception\DependencyException
     */
    public function authenticate()
    {
        if (
            $this->cache &&
            $this->cache->hasItem('token') &&
            $token = $this->cache->getItem('token')->get()
        ) {
            $this->token = $token;

            return (bool)$this->token;
        }

        if (!$this->authenticationStrategy) {
            throw new DependencyException(sprintf(
                'Specify authentication strategy before calling this method (%s).',
                __METHOD__
            ));
        }

        $this->token = $this->authenticationStrategy->authenticate();

        if ($this->cache) {
            $tokenItem = (new CacheItem('token'))->set($this->token)->expiresAfter($this->cacheTtl ?: 3600);

            $this->cache->save($tokenItem);
        }

        return (bool)$this->token;
    }

    /**
     * @param string $url
     * @param array  $options
     *
     * @return array
     * @throws \Vault\Exception\ServerException
     * @throws \Vault\Exception\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function get($url = null, array $options = [])
    {
        return $this->send(new Request('GET', $url), $options);
    }

    /**
     * @param RequestInterface $request
     * @param array            $options
     *
     * @return array
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Vault\Exception\ClientException
     * @throws \Vault\Exception\ServerException
     */
    public function send(RequestInterface $request, array $options = [])
    {
        if ($this->token) {
            $request = $request->withHeader('X-Vault-Token', $this->token);
        }

        $this->logger->info(sprintf('%s "%s"', $request->getMethod(), $request->getUri()));
        $this->logger->debug(sprintf(
            "Request: \n%s\n%s\n%s",
            $request->getUri(),
            $request->getMethod(),
            json_encode($request->getHeaders())
        ));

        try {
            $response = $this->transport->send($request, $options);
        } catch (TransferException $e) {
            $message = sprintf('Something went wrong when calling Vault (%s).', $e->getMessage());

            $this->logger->error($message);

            throw new ServerException($message);
        }

        $this->logger->debug(sprintf("Response: \n%s", (string)$response->getBody()));

        $this->checkResponse($response);

        return json_decode((string)$response->getBody(), true);
    }

    /**
     * @param ResponseInterface $response
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
     * @param string $url
     * @param array  $options
     *
     * @return array
     * @throws \Vault\Exception\ServerException
     * @throws \Vault\Exception\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function head($url, array $options = [])
    {
        return $this->send(new Request('HEAD', $url), $options);
    }

    /**
     * @param string $url
     * @param array  $options
     *
     * @return array
     * @throws \Vault\Exception\ServerException
     * @throws \Vault\Exception\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function delete($url, array $options = [])
    {
        return $this->send(new Request('DELETE', $url), $options);
    }

    /**
     * @param string $url
     * @param array  $options
     *
     * @return array
     * @throws \Vault\Exception\ServerException
     * @throws \Vault\Exception\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function put($url, array $options = [])
    {
        return $this->send(new Request('PUT', $url), $options);
    }

    /**
     * @param string $url
     * @param array  $options
     *
     * @return array
     * @throws \Vault\Exception\ServerException
     * @throws \Vault\Exception\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function patch($url, array $options = [])
    {
        return $this->send(new Request('PATCH', $url), $options);
    }

    /**
     * @param string $url
     * @param array  $options
     *
     * @return array
     * @throws \Vault\Exception\ServerException
     * @throws \Vault\Exception\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function post($url, array $options = [])
    {
        return $this->send(new Request('POST', $url), $options);
    }

    /**
     * @param string $url
     * @param array  $options
     *
     * @return array
     * @throws \Vault\Exception\ServerException
     * @throws \Vault\Exception\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function options($url, array $options = [])
    {
        return $this->send(new Request('OPTIONS', $url), $options);
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     *
     * @return $this
     */
    public function setToken($token)
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
     * @return int
     */
    public function getCacheTtl()
    {
        return $this->cacheTtl;
    }

    /**
     * @param int $cacheTtl
     *
     * @return $this
     */
    public function setCacheTtl($cacheTtl)
    {
        $this->cacheTtl = $cacheTtl;

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
}