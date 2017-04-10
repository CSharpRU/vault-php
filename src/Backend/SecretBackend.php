<?php

namespace Vault\Backend;

/**
 * Class SecretBackend
 *
 * @package Vault\Backend
 */
class SecretBackend extends AbstractBackend
{
    /**
     * @param string $path
     *
     * @return array
     *
     * @throws \Vault\Exception\ServerException
     * @throws \Vault\Exception\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function read($path)
    {
        return array_get($this->client->get($this->buildPath($path)), 'data');
    }

    /**
     * @param $path
     *
     * @return string
     */
    private function buildPath($path)
    {
        return sprintf('/v1/secret/%s', $path);
    }

    /**
     * @param string $path
     * @param array  $data
     *
     * @return bool
     *
     * @throws \Vault\Exception\ServerException
     * @throws \Vault\Exception\ClientException
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
     * @throws \Vault\Exception\ServerException
     * @throws \Vault\Exception\ClientException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function revoke($path)
    {
        $this->client->delete($this->buildPath($path));

        return true;
    }
}