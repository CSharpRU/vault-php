<?php

namespace Vault;

use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\MessageFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Vault\Builders\ResponseBuilder;
use Vault\Exceptions\ServerException;
use Vault\Models\Token;
use Vault\ResponseModels\Response;

/**
 * Class BaseClient
 *
 * @package Vault
 */
abstract class BaseClient implements LoggerAwareInterface
{
    const VERSION_1 = 'v1';

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
     * @var UriInterface
     */
    protected $baseUri;

    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var ResponseBuilder
     */
    protected $responseBuilder;

    /**
     * Client constructor.
     *
     * @param UriInterface         $baseUri
     * @param HttpClient|null      $httpClient
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        UriInterface $baseUri,
        HttpClient $httpClient = null,
        LoggerInterface $logger = null
    ) {
        $this->baseUri = $baseUri;
        $this->httpClient = $httpClient ?: HttpClientDiscovery::find();
        $this->messageFactory = MessageFactoryDiscovery::find();
        $this->logger = $logger ?: new NullLogger();
        $this->responseBuilder = new ResponseBuilder();
    }

    /**
     * @param string $path
     *
     * @return Response
     *
     * @throws \Http\Client\Exception
     */
    public function head($path)
    {
        return $this->responseBuilder->build($this->send('HEAD', $path));
    }

    /**
     * @param string $method
     * @param string $path
     * @param mixed  $body
     *
     * @return ResponseInterface
     *
     * @throws \Http\Client\Exception
     */
    public function send($method, $path, $body = null)
    {
        $headers = [
            'User-Agent' => 'VaultPHP/1.0.0',
            'Content-Type' => 'application/json',
        ];

        if ($this->token) {
            $headers['X-Vault-Token'] = $this->token->getAuth()->getClientToken();
        }

        $message = $this->messageFactory->createRequest(
            strtoupper($method),
            $this->baseUri->withPath($path),
            $headers,
            $body
        );

        $this->logger->debug('Request.', [
            'method' => $message->getMethod(),
            'uri' => $message->getUri(),
            'headers' => $message->getHeaders(),
            'body' => $message->getBody()->getContents(),
        ]);

        try {
            $response = $this->httpClient->sendRequest($message);
        } catch (\Exception $e) {
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

        return $response;
    }

    /**
     * @param string $path
     *
     * @return Response
     *
     * @throws \Http\Client\Exception
     */
    public function get($path = null)
    {
        return $this->responseBuilder->build($this->send('GET', $path));
    }

    /**
     * @param string $path
     * @param mixed  $body
     *
     * @return Response
     *
     * @throws \Http\Client\Exception
     */
    public function put($path, $body = null)
    {
        return $this->responseBuilder->build($this->send('PUT', $path, $body));
    }

    /**
     * @param string $path
     * @param mixed  $body
     *
     * @return Response
     *
     * @throws \Http\Client\Exception
     */
    public function patch($path, $body = null)
    {
        return $this->responseBuilder->build($this->send('PATCH', $path, $body));
    }

    /**
     * @param string $path
     *
     * @return Response
     *
     * @throws \Http\Client\Exception
     */
    public function options($path)
    {
        return $this->responseBuilder->build($this->send('OPTIONS', $path));
    }

    /**
     * @param string $path
     * @param mixed  $body
     *
     * @return Response
     *
     * @throws \Http\Client\Exception
     */
    public function post($path, $body = null)
    {
        return $this->responseBuilder->build($this->send('POST', $path, $body));
    }

    /**
     * @param string $path
     *
     * @return Response
     *
     * @throws \Http\Client\Exception
     */
    public function delete($path)
    {
        return $this->responseBuilder->build($this->send('DELETE', $path));
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
     * @return UriInterface
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * @param UriInterface $baseUri
     *
     * @return $this
     */
    public function setBaseUri($baseUri)
    {
        $this->baseUri = $baseUri;

        return $this;
    }

    /**
     * @return HttpClient
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * @param HttpClient $httpClient
     *
     * @return $this
     */
    public function setHttpClient($httpClient)
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * @return MessageFactory
     */
    public function getMessageFactory()
    {
        return $this->messageFactory;
    }

    /**
     * @param MessageFactory $messageFactory
     *
     * @return $this
     */
    public function setMessageFactory($messageFactory)
    {
        $this->messageFactory = $messageFactory;

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
