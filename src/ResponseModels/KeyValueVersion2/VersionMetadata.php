<?php

namespace Vault\ResponseModels\KeyValueVersion2;

use Vault\BaseObject;

/**
 * Class VersionMetadata
 *
 * @package Vault\Model\KeyValueVersion2
 */
class VersionMetadata extends BaseObject
{
    /**
     * @var string
     */
    protected $createdTime;

    /**
     * @var array|null
     */
    protected $customMetadata;

    /**
     * @var string|null
     */
    protected $deletionTime;

    /**
     * @var bool
     */
    protected $destroyed;

    /**
     * @var int
     */
    protected $version;

    /**
     * @return string
     */
    public function getCreatedTime(): string
    {
        return $this->createdTime;
    }

    /**
     * @return array|null
     */
    public function getCustomMetadata(): ?array
    {
        return $this->customMetadata;
    }

    /**
     * @return string|null
     */
    public function getDeletionTime(): ?string
    {
        return $this->deletionTime;
    }

    /**
     * @return bool
     */
    public function isDestroyed(): bool
    {
        return $this->destroyed;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }
}
