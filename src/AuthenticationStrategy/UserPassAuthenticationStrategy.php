<?php

namespace Vault\AuthenticationStrategy;

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
     * Returns token for further interactions with Vault.
     *
     * @return string
     * @throws \Vault\Exception\ServerException
     * @throws \Vault\Exception\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function authenticate()
    {
        $json = $this->client->post(sprintf('/v1/auth/userpass/login/%s', $this->username), [
            'json' => ['password' => $this->password],
        ]);

        return array_get($json, 'auth.client_token');
    }
}