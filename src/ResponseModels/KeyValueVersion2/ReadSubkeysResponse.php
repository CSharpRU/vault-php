<?php

namespace Vault\ResponseModels\KeyValueVersion2;

use Vault\BaseObject;
use Vault\ResponseModels\KeyValueVersion2\Traits\MetadataTrait;

/**
 * Class ReadSubkeysResponse
 *
 * @package Vault\Model\KeyValueVersion2
 */
class ReadSubkeysResponse extends BaseObject
{
    use MetadataTrait;

    /**
     * @var array
     */
    protected $subkeys = [];
    
    /**
     * @return array
     */
    public function getSubkeys(): array
    {
        return $this->subkeys;
    }
}
