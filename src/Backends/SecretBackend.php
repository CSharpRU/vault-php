<?php

namespace Vault\Backends;

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
     * @return string
     */
    protected function buildPath($path)
    {
        return sprintf('/v1/secret/%s', $path);
    }
}