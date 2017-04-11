<?php

namespace Vault\AuthenticationStrategies;

use Vault\Client;
use Vault\ResponseModels\Auth;

/**
 * Interface AuthenticationStrategy
 *
 * @package Vault\AuthenticationStrategy
 */
interface AuthenticationStrategy
{
    /**
     * Returns auth for further interactions with Vault.
     *
     * @return Auth
     */
    public function authenticate();

    /**
     * @return Client
     */
    public function getClient();

    /**
     * @param Client $client
     *
     * @return $this
     */
    public function setClient(Client $client);
}