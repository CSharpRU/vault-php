<?php

namespace Vault\Backends;

use Vault\Client;
use Vault\ResponseModels\Response;

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
     * @return Response
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

    /**
     * @return Client
     */
    public function getClient();

    /**
     * @param Client $client
     *
     * @return $this
     */
    public function setClient($client);
}