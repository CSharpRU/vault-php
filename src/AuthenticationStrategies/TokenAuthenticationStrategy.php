<?php

namespace Vault\AuthenticationStrategies;

use Vault\ResponseModels\Auth;

/**
 * Class TokenAuthenticationStrategy
 *
 * @package Vault\AuthenticationStrategy
 */
class TokenAuthenticationStrategy extends AbstractAuthenticationStrategy
{
    /**
     * @var string
     */
    protected $token;

    /**
     * TokenAuthenticationStrategy constructor.
     *
     * @param string $token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Returns auth for further interactions with Vault.
     *
     * @return Auth
     */
    public function authenticate(): ?Auth
    {
        return new Auth(['clientToken' => $this->token]);
    }
}
