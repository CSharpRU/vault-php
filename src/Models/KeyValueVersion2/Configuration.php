<?php

namespace Vault\Models\KeyValueVersion2;

use Vault\BaseObject;

/**
 * Class Configuration
 *
 * @package Vault\Models\KeyValueVersion2
 */
class Configuration extends BaseObject
{
    /**
     * If true all keys will require the cas parameter to be set on all write requests
     * 
     * @var bool
     */
    protected $casRequired = false;

    /**
     * Specifies the length of time before a version is deleted. Accepts duration format strings
     * 
     * @var string
     */
    protected $deleteVersionAfter = '0s';

    /**
     * The number of versions to keep per key. When 0 is used or the value is unset, Vault will keep 10 versions
     * 
     * @var int
     */
    protected $maxVersions = 0;

    /**
     * Get {@see $casRequired cas_required}
     * 
     * @return bool
     */
    public function isCasRequired(): bool
    {
        return $this->casRequired;
    }

    /**
     * Set {@see $casRequired cas_required}
     * 
     * @param bool $casRequired
     * 
     * @return $this
     */
    public function setCasRequired(bool $casRequired): self
    {
        $this->casRequired = $casRequired;

        return $this;
    }

    /**
     * Get {@see $deleteVersionAfter delete_version_after}
     * 
     * @return string
     */
    public function getDeleteVersionAfter(): string
    {
        return $this->deleteVersionAfter;
    }

    /**
     * Set {@see $deleteVersionAfter delete_version_after}
     * 
     * @param string $deleteVersionAfter
     * 
     * @return $this
     */
    public function setDeleteVersionAfter(string $deleteVersionAfter): self
    {
        $this->deleteVersionAfter = $deleteVersionAfter;

        return $this;
    }

    /**
     * Get {@see $maxVersions max_versions}
     * 
     * @return int
     */
    public function getMaxVersions(): int
    {
        return $this->maxVersions;
    }

    /**
     * Set {@see $maxVersions max_versions}
     * 
     * @param int $maxVersions
     * 
     * @return $this
     */
    public function setMaxVersions(int $maxVersions): self
    {
        $this->maxVersions = $maxVersions;

        return $this;
    }
}
