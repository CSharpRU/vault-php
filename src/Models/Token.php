<?php

namespace Vault\Models;

use Vault\BaseObject;
use Vault\ResponseModels\Auth;

/**
 * Class Token
 *
 * @package Vault\Models
 */
class Token extends BaseObject
{
    /**
     * @var Auth|null
     */
    protected $auth;

    /**
     * @var string|null
     */
    protected $accessor;

    /**
     * @var int|null
     */
    protected $creationTime;

    /**
     * @var int|null
     */
    protected $creationTtl;

    /**
     * @var string|null
     */
    protected $displayName;

    /**
     * @var int|null
     */
    protected $explicitMaxTtl;

    /**
     * @var string|null
     */
    protected $id;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @var int
     */
    protected $numUses = 0;

    /**
     * @var bool
     */
    protected $orphan = false;

    /**
     * @var string|null
     */
    protected $path;

    /**
     * @var array
     */
    protected $policies = [];

    /**
     * @var int|null
     */
    protected $ttl;

    /**
     * @return Auth|null
     */
    public function getAuth(): ?Auth
    {
        return $this->auth;
    }

    /**
     * @return string|null
     */
    public function getAccessor(): ?string
    {
        return $this->accessor;
    }

    /**
     * @return int|null
     */
    public function getCreationTime(): ?int
    {
        return $this->creationTime;
    }

    /**
     * @return int|null
     */
    public function getCreationTtl(): ?int
    {
        return $this->creationTtl;
    }

    /**
     * @return string|null
     */
    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    /**
     * @return int|null
     */
    public function getExplicitMaxTtl(): ?int
    {
        return $this->explicitMaxTtl;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @return int
     */
    public function getNumUses(): int
    {
        return $this->numUses;
    }

    /**
     * @return bool
     */
    public function isOrphan(): bool
    {
        return $this->orphan;
    }

    /**
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getPolicies(): array
    {
        return $this->policies;
    }

    /**
     * @return int|null
     */
    public function getTtl(): ?int
    {
        return $this->ttl;
    }
}
