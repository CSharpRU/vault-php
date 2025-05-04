<?php

namespace Vault\ResponseModels\KeyValueVersion2;

use Vault\BaseObject;

/**
 * Class ListResponse
 *
 * @package Vault\Model\KeyValueVersion2
 */
class ListResponse extends BaseObject
{
    /**
     * @var array
     */
    protected $keys;
    
    /**
     * @return array
     */
    public function getKeys(): array
    {
        return $this->keys;
    }
}
