<?php

namespace Vault\ResponseModels\KeyValueVersion1;

use Vault\BaseObject;

/**
 * Class ListResponse
 *
 * @package Vault\Model\KeyValueVersion1
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
