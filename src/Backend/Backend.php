<?php

namespace Vault\Backend;

/**
 * Interface Backend
 *
 * @package Vault\Backend
 */
interface Backend
{
    /**
     * @param string $path
     *
     * @return array
     */
    public function read($path);

    /**
     * @param string $path
     * @param array  $data
     *
     * @return bool
     */
    public function write($path, array $data = []);

    /**
     * @param string $path
     *
     * @return bool
     */
    public function revoke($path);
}