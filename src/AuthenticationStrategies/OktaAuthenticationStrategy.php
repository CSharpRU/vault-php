<?php

namespace Vault\AuthenticationStrategies;

/**
 * Class OktaAuthenticationStrategy
 *
 * @package Vault\AuthenticationStrategy
 */
class OktaAuthenticationStrategy extends AbstractPathAuthenticationStrategy
{
    /**
     * @var string
     */
    protected $methodPathSegment = 'okta';
}
