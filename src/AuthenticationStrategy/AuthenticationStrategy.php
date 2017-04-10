<?php

namespace Vault\AuthenticationStrategy;

use Vault\Client;

/**
 * Interface AuthenticationStrategy
 *
 * @package Vault\AuthenticationStrategy
 */
interface AuthenticationStrategy
{
    /**
     * Returns token for further interactions with Vault.
     *
     * @return string
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