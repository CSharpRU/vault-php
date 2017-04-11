<?php

namespace Vault\ResponseModels\Traits;

/**
 * Class LeaseTrait
 *
 * @package Vault\Model
 */
trait LeaseTrait
{
    /**
     * @var int
     */
    protected $leaseId;

    /**
     * @var int
     */
    protected $leaseDuration;

    /**
     * @var bool
     */
    protected $renewable;

    /**
     * @return int
     */
    public function getLeaseId()
    {
        return $this->leaseId;
    }

    /**
     * @return int
     */
    public function getLeaseDuration()
    {
        return $this->leaseDuration;
    }

    /**
     * @return bool
     */
    public function isRenewable()
    {
        return $this->renewable;
    }
}