<?php

namespace Vault;

use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Vault\Builders\ResponseBuilder;
use Vault\Exceptions\ServerException;
use Vault\Models\Token;
use Vault\ResponseModels\Response;
use Vault\Transports\Transport;

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
     * @var Transport
     */
    protected $transport;

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

        return $response;
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
