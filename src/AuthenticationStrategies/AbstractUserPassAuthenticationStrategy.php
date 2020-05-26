<?php

namespace Vault\AuthenticationStrategies;

use Vault\Exceptions\AuthenticationException;
use Vault\ResponseModels\Auth;

/**
 * Class AbstractUserPassAuthenticationStrategy
 * @package Vault\AuthenticationStrategies
 */
abstract class AbstractUserPassAuthenticationStrategy extends AbstractAuthenticationStrategy
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
     * @var string
     */
    protected $methodPathSegment;

    /**
     * AbstractUserPassAuthenticationStrategy constructor.
     *
     * @param string $username    The Username used to authenticate to Vault
     * @param string $password    The Password used to authenticate to Vault
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Set the Path-Segment the Auth-Method is mounted at
     *
     * @param string $path
     * @return $this
     */
    public function setMethodPathSegment(string $path): self
    {
        $this->methodPathSegment = trim($path);

        return $this;
    }

    /**
     * Returns auth for further interactions with Vault.
     *
     * @return Auth
     * @throws AuthenticationException
     * @throws ClientExceptionInterface
     */
    public function authenticate(): Auth
    {
        if (strlen($this->methodPathSegment) >= 1) {
            $response = $this->client->write(
                sprintf('/auth/%s/login/%s', $this->methodPathSegment, $this->username),
                ['password' => $this->password]
            );

            return $response->getAuth();
        } else {
            throw new AuthenticationException('No Method-Path given.', 1590235728);
        }
    }
}
