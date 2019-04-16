<?php

namespace Vault\AuthenticationStrategies;

use Vault\ResponseModels\Auth;

/**
 * Class UserPassAuthenticationStrategy
 *
 * @package Vault\AuthenticationStrategy
 */
class UserPassAuthenticationStrategy extends AbstractAuthenticationStrategy
{
    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * UserPassAuthenticationStrategy constructor.
     *
     * @param string $username
     * @param string $password
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Returns auth for further interactions with Vault.
     *
     * @return Auth
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function authenticate(): Auth
    {
        $response = $this->client->write(
            sprintf('/auth/userpass/login/%s', $this->username),
            ['password' => $this->password]
        );

        return $response->getAuth();
    }
}
