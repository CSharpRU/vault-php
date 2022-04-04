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
     * @var string|null
     */
    protected $leaseId;

    /**
     * @var int|null
     */
    protected $leaseDuration;

    /**
     * @var bool|null
     */
    protected $renewable;

    /**
     * @return string|null
     */
    public function getLeaseId(): ?string
    {
        return $this->leaseId;
    }

    /**
     * @return int|null
     */
    public function getLeaseDuration(): ?int
    {
        return $this->leaseDuration;
    }

    /**
     * @return bool|null
     */
    public function isRenewable(): ?bool
    {
        return $this->renewable;
    }
}
