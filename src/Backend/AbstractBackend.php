<?php

namespace Vault\Backend;

use Vault\Client;

/**
 * Class AbstractBackend
 *
 * @package Vault\Backend
 */
abstract class AbstractBackend implements Backend
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * AbstractBackend constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param Client $client
     *
     * @return $this
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }
}