<?php

namespace Vault\Backends;

use Vault\Client;
use Vault\ResponseModels\Response;

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
     * @param string $path
     *
     * @return Response
     *
     * @throws \Vault\Exceptions\ServerException
     * @throws \Vault\Exceptions\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function read($path)
    {
        return $this->client->get($this->buildPath($path));
    }

    /**
     * @param string $path
     *
     * @return string
     */
    protected abstract function buildPath($path);

    /**
     * @param string $path
     * @param array  $data
     *
     * @return bool
     *
     * @throws \Vault\Exceptions\ServerException
     * @throws \Vault\Exceptions\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function write($path, array $data = [])
    {
        $this->client->post($this->buildPath($path), ['json' => $data]);

        return true;
    }

    /**
     * @param string $path
     *
     * @return bool
     *
     * @throws \Vault\Exceptions\ServerException
     * @throws \Vault\Exceptions\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function revoke($path)
    {
        $this->client->delete($this->buildPath($path));

        return true;
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