<?php

namespace Vault\AuthenticationStrategies;

use Psr\Http\Client\ClientExceptionInterface;

/**
 * Class RadiusAuthenticationStrategy
 *
 * @package Vault\AuthenticationStrategy
 */
class RadiusAuthenticationStrategy extends AbstractUserPassAuthenticationStrategy
{
    /**
     * @inheritDoc
     */
    public function __construct($username, $password, $methodPathSegment = 'radius')
    {
        parent::__construct($username, $password);

        $this->setMethodPathSegment($methodPathSegment);
    }
}
