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
     * @var string|null
     */
    protected $clientToken;

    /**
     * @return string|null
     */
    public function getClientToken(): ?string
    {
        return $this->clientToken;
    }
}
