<?php

namespace Vault\Exceptions;

/**
 * Class AbstractResponseException
 *
 * @package Vault\Exception
 */
class AbstractResponseException extends \RuntimeException
{
    /**
     * @var null
     */
    public $response;

    public function __construct($message, $code = null, $response = null)
    {
        parent::__construct($message, $code);

        $this->response = $response;
    }

    /**
     * @return null
     */
    public function getResponse()
    {
        return $this->response;
    }
}
