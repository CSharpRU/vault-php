<?php

namespace Vault\Exceptions;

use Exception;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Throwable;

/**
 * Class RequestException
 *
 * @package Vault\Exceptions
 */
class RequestException extends Exception implements RequestExceptionInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    public function __construct(
        string $message = '',
        int $code = 0,
        Throwable $previous = null,
        RequestInterface $request = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->request = $request;
    }

    /**
     * Returns the request.
     *
     * The request object MAY be a different object from the one passed to ClientInterface::sendRequest()
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
