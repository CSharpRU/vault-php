.. Vault PHP Client documentation master file, created by
   sphinx-quickstart on Thu Aug 10 14:35:27 2017.
   You can adapt this file completely to your liking, but it should at least
   contain the root `toctree` directive.

Welcome to Vault PHP Client's documentation!
============================================

.. toctree::
   :maxdepth: 2
   :caption: Contents:

This is a PHP client for Vault - a tool for managing secrets.

Quick start
-----------

.. code-block:: php

    <?php

    use Vault\AuthenticationStrategies\AppRoleAuthenticationStrategy;
    use Vault\AuthenticationStrategies\UserPassAuthenticationStrategy;
    use Vault\Client;
    use VaultTransports\Guzzle5Transport;
    use VaultTransports\Guzzle6Transport;

    // Creating the client
    $client = new Client(new Guzzle5Transport()); //Using Guzzle5 Transport
    $client = new Client(new Guzzle6Transport()); //Using Guzzle6 Transport
    $client = new Client(new Guzzle5Transport(['base_url' => 'http://10.10.3.39:8200'])); // Passing a custom url

    // Authenticating using userpass auth backend.

    $authenticated = $client
        ->setAuthenticationStrategy(new UserPassAuthenticationStrategy('test', 'test'))
        ->authenticate();

    // Authenticating using approle auth backend.

    $authenticated = $client
        ->setAuthenticationStrategy(new AppRoleAuthenticationStrategy(
            'd4131206-384f-75fa-11d6-55d1d63c07c0',
            'cac86a12-c566-3932-09f3-5823ccdfa606'
        ))
        ->authenticate();

Indices and tables
==================

* :ref:`search`
