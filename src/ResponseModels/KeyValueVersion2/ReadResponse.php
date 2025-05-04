<?php

namespace Vault\ResponseModels\KeyValueVersion2;

use Vault\BaseObject;
use Vault\ResponseModels\KeyValueVersion2\Traits\MetadataTrait;

/**
 * Class ReadResponse
 *
 * @package Vault\Model\KeyValueVersion2
 */
class ReadResponse extends BaseObject
{
    use MetadataTrait;

    /**
     * @var array
     */
    protected $data = [];
    
    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
