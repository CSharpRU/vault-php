<?php

namespace Vault\Models\KeyValueVersion2;

use Vault\BaseObject;

/**
 * Class WriteOptions
 *
 * @package Vault\Models\KeyValueVersion2
 */
class WriteOptions extends BaseObject
{
    /**
     * This flag is required if cas_required is set to true on either the secret or the engine's config. If not set the write will be allowed. In order for a write to be successful, cas must be set to the current version of the secret. If set to 0 a write will only be allowed if the key doesn't exist as unset keys do not have any version information.
     * 
     * @var int|null
     */
    protected $cas;

    /**
     * Get {@see $cas cas}
     * 
     * @return int|null
     */
    public function getCas(): ?int
    {
        return $this->cas;
    }

    /**
     * Set {@see $cas cas}
     * 
     * @param int $cas
     * 
     * @return $this
     */
    public function setCas(?int $cas): self
    {
        $this->cas = $cas;

        return $this;
    }
}
