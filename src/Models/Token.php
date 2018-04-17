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
     * @var Auth
     */
    protected $auth;

    /**
     * @var string
     */
    protected $accessor;

    /**
     * @var int
     */
    protected $creationTime;

    /**
     * @var int
     */
    protected $creationTtl;

    /**
     * @var string
     */
    protected $displayName;

    /**
     * @var int
     */
    protected $explicitMaxTtl;

    /**
     * @var string
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
     * @var string
     */
    protected $path;

    /**
     * @var array
     */
    protected $policies = [];

    /**
     * @var int
     */
    protected $ttl;

    /**
     * @return Auth
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * @return string
     */
    public function getAccessor()
    {
        return $this->accessor;
    }

    /**
     * @return int
     */
    public function getCreationTime()
    {
        return $this->creationTime;
    }

    /**
     * @return int
     */
    public function getCreationTtl()
    {
        return $this->creationTtl;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @return int
     */
    public function getExplicitMaxTtl()
    {
        return $this->explicitMaxTtl;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @return int
     */
    public function getNumUses()
    {
        return $this->numUses;
    }

    /**
     * @return bool
     */
    public function isOrphan()
    {
        return $this->orphan;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getPolicies()
    {
        return $this->policies;
    }

    /**
     * @return int
     */
    public function getTtl()
    {
        return $this->ttl;
    }
}
