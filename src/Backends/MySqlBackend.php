<?php

namespace Vault\Backends;

/**
 * Class MySqlBackend
 *
 * @package Vault\Backend
 */
class MySqlBackend extends AbstractBackend
{
    /**
     * @param string $path
     *
     * @return string
     */
    private function buildPath($path)
    {
        return sprintf('/v1/mysql/%s', $path);
    }
}