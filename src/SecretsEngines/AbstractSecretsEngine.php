<?php

namespace Vault\SecretsEngines;

use Vault\Client;
use Vault\Exceptions\RuntimeException;

/**
 * Class AbstractSecretsEngine
 *
 * @package Vault\SecretsEngine
 */
abstract class AbstractSecretsEngine
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $mount;

    public function __construct(Client $client, string $mount)
    {
        $this->client = $client;
        if (empty($mount)) {
            throw new RuntimeException('Secrets Engine require not-empty mount path');
        }
        if ($mount[0] !== '/') {
            $mount = '/'.$mount;
        }
        $this->mount = $mount;
    }

    public function buildPath(string $path): string
    {
        return sprintf('%s/%s', $this->client->buildPath($this->mount), $path);
    }

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
    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return string
     */
    public function getMount(): string
    {
        return $this->mount;
    }
}
