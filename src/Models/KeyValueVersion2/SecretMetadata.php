<?php

namespace Vault\Models\KeyValueVersion2;

/**
 * Class SecretMetadata
 *
 * @package Vault\Models\KeyValueVersion2
 */
class SecretMetadata extends Configuration
{
    /**
     * A map of arbitrary string to string valued user-provided metadata meant to describe the secret
     * 
     * @var array|null
     */
    protected $customMetadata;

    /**
     * Get {@see $customMetadata custom_metadata}
     * 
     * @return array|null
     */
    public function getCustomMetadata(): ?array
    {
        return $this->customMetadata;
    }

    /**
     * Set {@see $customMetadata custom_metadata}
     * 
     * @param array|null $customMetadata
     * 
     * @return $this
     */
    public function setCustomMetadata(?array $customMetadata): self
    {
        $this->customMetadata = $customMetadata;

        return $this;
    }
}
