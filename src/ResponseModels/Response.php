<?php

namespace Vault\ResponseModels;

use Vault\BaseObject;
use Vault\ResponseModels\Traits\LeaseTrait;

/**
 * Class Response
 *
 * @package Vault\Model
 */
class Response extends BaseObject
{
    use LeaseTrait;

    /**
     * @var string
     */
    protected $requestId;

    /**
     * @var Auth
     */
    protected $auth;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @return string
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * @return Auth
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
