<?php

namespace Vault\Backends;

/**
 * Class PostgreSqlBackend
 *
 * @package Vault\Backend
 */
class PostgreSqlBackend extends AbstractBackend
{
    /**
     * @param string $path
     *
     * @return string
     */
    protected function buildPath($path)
    {
        return sprintf('/v1/postgresql/%s', $path);
    }
}