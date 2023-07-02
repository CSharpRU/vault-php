<?php

namespace Vault\ResponseModels\KeyValueVersion2;

use Vault\Models\KeyValueVersion2\SecretMetadata;

/**
 * Class ReadMetadataResponse
 *
 * @package Vault\Model\KeyValueVersion2
 */
class ReadMetadataResponse extends SecretMetadata
{
    /**
     * @var string
     */
    protected $createdTime;

    /**
     * @var int
     */
    protected $currentVersion;

    /**
     * @var int
     */
    protected $oldestVersion;

    /**
     * @var string|null
     */
    protected $updatedTime;

    /**
     * @var VersionMetadata[]
     */
    protected $versions = [];

    /**
     * @return string
     */
    public function getCreatedTime(): string
    {
        return $this->createdTime;
    }

    /**
     * @return int
     */
    public function getCurrentVersion(): int
    {
        return $this->currentVersion;
    }

    /**
     * @return int
     */
    public function getOldestVersion(): int
    {
        return $this->oldestVersion;
    }

    /**
     * @return string|null
     */
    public function getUpdatedTime(): ?string
    {
        return $this->updatedTime;
    }

    /**
     * @return VersionMetadata[]
     */
    public function getVersions(): array
    {
        return $this->versions;
    }
}
