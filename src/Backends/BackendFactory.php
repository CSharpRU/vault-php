<?php

namespace Vault\Backends;

use Vault\Client;
use Vault\Exceptions\ClassNotFoundException;

/**
 * Class BackendFactory
 *
 * @package Vault\Backend
 */
class BackendFactory
{
    const BACKEND_SECRET = 'secret';
    const BACKEND_MYSQL = 'mysql';
    const BACKEND_POSTGRESQL = 'postgresql';

    /**
     * @var array
     */
    protected static $map = [
        self::BACKEND_SECRET => SecretBackend::class,
        self::BACKEND_MYSQL => MySqlBackend::class,
        self::BACKEND_POSTGRESQL => PostgreSqlBackend::class,
    ];

    /**
     * @param Client $client
     * @param string $backend
     *
     * @return Backend
     * @throws \Vault\Exceptions\ClassNotFoundException
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