<?php

namespace Vault\ResponseModels;

use Vault\BaseObject;
use Vault\ResponseModels\Traits\LeaseTrait;

/**
 * Class Auth
 *
 * @package Vault\Model
 */
class Auth extends BaseObject
{
    use LeaseTrait;

    /**
     * @var string
     */
    protected $clientToken;

    /**
     * @return string
     */
    public function getClientToken()
    {
        return $this->clientToken;
    }
}
