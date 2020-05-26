<?php

namespace Vault\AuthenticationStrategies;

/**
 * Class UserPassAuthenticationStrategy
 *
 * @package Vault\AuthenticationStrategy
 */
class UserPassAuthenticationStrategy extends AbstractPathAuthenticationStrategy
{
    /**
     * @var string
     */
    protected $methodPathSegment = 'userpass';
}
