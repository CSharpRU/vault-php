<?php

namespace Vault;

use InvalidArgumentException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Vault\Builders\ResponseBuilder;
use Vault\Exceptions\RequestException;
use Vault\Models\Token;
use Vault\ResponseModels\Response;

/**
 * Class BaseClient
 *
 * @package Vault
 */
abstract class BaseClient implements LoggerAwareInterface
{
    public const VERSION_1 = 'v1';

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
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var RequestFactoryInterface
     */
    protected $requestFactory;

    /**
     * @var StreamFactoryInterface
     */
    protected $streamFactory;

    /**
     * @var ResponseBuilder
     */
    protected $responseBuilder;

    /**
     * Client constructor.
     *
     * @param UriInterface $baseUri
     * @param ClientInterface $client
     * @param RequestFactoryInterface $requestFactory
     * @param StreamFactoryInterface $streamFactory
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        UriInterface $baseUri,
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        LoggerInterface $logger = null
    ) {
        $this->baseUri = $baseUri;
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->logger = $logger ?: new NullLogger();
        $this->responseBuilder = new ResponseBuilder();
    }

    /**
     * @param string $path
     *
     * @return Response
     * @throws InvalidArgumentException
     * @throws ClientExceptionInterface
     */
    public function head(string $path): Response
    {
        return $this->responseBuilder->build($this->send('HEAD', $path));
    }

    /**
     * @param string $method
     * @param string $path
     * @param string $body
     *
     * @return ResponseInterface
     * @throws RequestException
     * @throws InvalidArgumentException
     */
    public function send(string $method, string $path, string $body = ''): ResponseInterface
    {
        $headers = [
            'User-Agent' => 'VaultPHP/1.0.0',
            'Content-Type' => 'application/json',
        ];

        if ($this->token) {
            $headers['X-Vault-Token'] = $this->token->getAuth()->getClientToken();
        }

        if (strpos($path, '?') !== false) {
            [$path, $query] = explode('?', $path, 2);
            $this->baseUri = $this->baseUri->withQuery($query);
        }

        $request = $this->requestFactory->createRequest(strtoupper($method), $this->baseUri->withPath($path));

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        $request = $request->withBody($this->streamFactory->createStream($body));

        $this->logger->debug('Request.', [
            'method' => $method,
            'uri' => $request->getUri(),
            'headers' => $headers,
            'body' => $body,
        ]);

        try {
            $response = $this->client->sendRequest($request);

            if ($response->getStatusCode() > 399) {
                throw new RequestException(
                    'Bad status received from Vault',
                    $response->getStatusCode(),
                    null,
                    $request
                );
            }
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('Something went wrong when calling Vault.', [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);

            $this->logger->debug('Trace.', ['exception' => $e]);

            throw new RequestException($e->getMessage(), $e->getCode(), $e, $request);
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
     * @throws InvalidArgumentException
     * @throws ClientExceptionInterface
     */
    public function list(string $path = ''): Response
    {
        return $this->responseBuilder->build($this->send('LIST', $path));
    }

    /**
     * @param string $path
     *
     * @return Response
     * @throws InvalidArgumentException
     * @throws ClientExceptionInterface
     */
    public function get(string $path = ''): Response
    {
        return $this->responseBuilder->build($this->send('GET', $path));
    }

    /**
     * @param string $path
     * @param string $body
     *
     * @return Response
     * @throws InvalidArgumentException
     * @throws ClientExceptionInterface
     */
    public function put(string $path, string $body = ''): Response
    {
        return $this->responseBuilder->build($this->send('PUT', $path, $body));
    }

    /**
     * @param string $path
     * @param string $body
     *
     * @return Response
     * @throws InvalidArgumentException
     * @throws ClientExceptionInterface
     */
    public function patch(string $path, string $body = ''): Response
    {
        return $this->responseBuilder->build($this->send('PATCH', $path, $body));
    }

    /**
     * @param string $path
     *
     * @return Response
     * @throws InvalidArgumentException
     * @throws ClientExceptionInterface
     */
    public function options(string $path): Response
    {
        return $this->responseBuilder->build($this->send('OPTIONS', $path));
    }

    /**
     * @param string $path
     * @param string $body
     *
     * @return Response
     * @throws InvalidArgumentException
     * @throws ClientExceptionInterface
     */
    public function post(string $path, string $body = ''): Response
    {
        return $this->responseBuilder->build($this->send('POST', $path, $body));
    }

    /**
     * @param string $path
     *
     * @return Response
     * @throws InvalidArgumentException
     * @throws ClientExceptionInterface
     */
    public function delete(string $path): Response
    {
        return $this->responseBuilder->build($this->send('DELETE', $path));
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @param string $version
     *
     * @return $this
     */
    public function setVersion(string $version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return Token
     */
    public function getToken(): Token
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
    public function getBaseUri(): UriInterface
    {
        return $this->baseUri;
    }

    /**
     * @param UriInterface $baseUri
     *
     * @return $this
     */
    public function setBaseUri(UriInterface $baseUri)
    {
        $this->baseUri = $baseUri;

        return $this;
    }

    /**
     * @return ClientInterface
     */
    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    /**
     * @param ClientInterface $client
     *
     * @return $this
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return RequestFactoryInterface
     */
    public function getRequestFactory(): RequestFactoryInterface
    {
        return $this->requestFactory;
    }

    /**
     * @param RequestFactoryInterface $requestFactory
     *
     * @return $this
     */
    public function setRequestFactory(RequestFactoryInterface $requestFactory)
    {
        $this->requestFactory = $requestFactory;

        return $this;
    }

    /**
     * @return StreamFactoryInterface
     */
    public function getStreamFactory(): StreamFactoryInterface
    {
        return $this->streamFactory;
    }

    /**
     * @param StreamFactoryInterface $streamFactory
     *
     * @return $this
     */
    public function setStreamFactory($streamFactory)
    {
        $this->streamFactory = $streamFactory;

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
    public function getResponseBuilder(): ResponseBuilder
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
