<?php

namespace Vault\ResponseModels\KeyValueVersion2\Traits;

use Vault\ResponseModels\KeyValueVersion2\VersionMetadata;

/**
 * Class MetadataTrait
 *
 * @package Vault\Model\KeyValueVersion2
 */
trait MetadataTrait
{
    /**
     * @var VersionMetadata
     */
    protected $metadata;
    
    /**
     * @return VersionMetadata
     */
    public function getMetadata(): VersionMetadata
    {
        return $this->metadata;
    }
}
