<?php

namespace Vault\AuthenticationStrategies;

/**
 * Class RadiusAuthenticationStrategy
 *
 * @package Vault\AuthenticationStrategy
 */
class RadiusAuthenticationStrategy extends AbstractPathAuthenticationStrategy
{
    /**
     * @var string
     */
    protected $methodPathSegment = 'radius';
}
