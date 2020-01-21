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
     * @var string|null
     */
    protected $requestId;

    /**
     * @var Auth|null
     */
    protected $auth;

    /**
     * @var array|null
     */
    protected $data = [];

    /**
     * @return string|null
     */
    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    /**
     * @return Auth|null
     */
    public function getAuth(): ?Auth
    {
        return $this->auth;
    }

    /**
     * @return array|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }
}
