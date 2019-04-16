<?php

namespace Vault\AuthenticationStrategies;

use Vault\Client;

/**
 * Class AbstractAuthenticationStrategy
 *
 * @package Vault\AuthenticationStrategy
 */
abstract class AbstractAuthenticationStrategy implements AuthenticationStrategy
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @param Client $client
     *
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }
}
