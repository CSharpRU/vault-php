<?php

namespace Vault\Backend;

use Vault\Client;
use Vault\Exception\ClassNotFoundException;

/**
 * Class BackendFactory
 *
 * @package Vault\Backend
 */
class BackendFactory
{
    const BACKEND_SECRET = 'secret';

    protected static $map = [
        self::BACKEND_SECRET => SecretBackend::class,
    ];

    /**
     * @param Client $client
     * @param string $backend
     *
     * @return Backend
     * @throws \Vault\Exception\ClassNotFoundException
     */
    public static function getBackend(Client $client, $backend)
    {
        $class = array_get(static::$map, $backend);

        if (!$class) {
            throw new ClassNotFoundException(sprintf('Cannot find class for %s backend', $backend));
        }

        return new $class($client);
    }
}