# Vault PHP client [![Build Status](https://travis-ci.org/CSharpRU/vault-php.svg?branch=master)](https://travis-ci.org/CSharpRU/vault-php) [![Codacy Badge](https://api.codacy.com/project/badge/Grade/0bf9f46a659844658d847c1b2ab01e8b)](https://www.codacy.com/app/c_sharp/vault-php?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=CSharpRU/vault-php&amp;utm_campaign=Badge_Grade) [![Read the Docs Badge](https://readthedocs.org/projects/vault-php/badge/?version=latest)](https://readthedocs.org/projects/vault-php/badge/?version=latest) 
> **Warning! This project is not production ready, however I'm using it inside a production project and it works fine.**
> **I could change versioning or break backward compatibility.**
> **Use it at your own risk.**

This is a PHP client for Vault - a tool for managing secrets.

## Installing / Getting started

Simply run this command within your directory with composer.json. 

```shell
composer require "csharpru/vault-php"
```

## Documentation

Latest documentation is available here: http://vault-php.readthedocs.io/en/latest/

### Examples
````

use Vault\Client;
use VaultTransports\Guzzle5Transport;
use VaultTransports\Guzzle6Transport;

// Creating the client
$client = new Client(new Guzzle5Transport()); //Using Guzzle5 Transport
$client = new Client(new Guzzle6Transport()); //Using Guzzle6 Transport
$client = new Client(new Guzzle5Transport(array('base_url' => 'http://10.10.3.39:8200'))); //Passsing a custom url

// Authenticating using userpass auth backend.
use Vault\AuthenticationStrategies\UserPassAuthenticationStrategy;

$authenticated = $client
                  ->setAuthenticationStrategy(new UserPassAuthenticationStrategy('test', 'test'))
                  ->authenticate();

// Authenticating using approle auth backend.
use Vault\AuthenticationStrategies\AppRoleAuthenticationStrategy;

$authenticated = $client
                  ->setAuthenticationStrategy(
                    new AppRoleAuthenticationStrategy(
                      'd4131206-384f-75fa-11d6-55d1d63c07c0', 
                      'cac86a12-c566-3932-09f3-5823ccdfa606'
                    ))
                  ->authenticate();

````

## Developing

If you want to contribute, execute following shell commands:

```shell
git clone https://github.com/CSharpRU/vault-php.git
cd vault-php/
composer install
```

Now you're ready to write tests and code.

## Features

* Supports different authentication backends (right now userpass only) with token caching and re-authentication.
* Different transports for different PHP versions.

## Contributing

If you'd like to contribute, please fork the repository and use a feature
branch. Pull requests are warmly welcome.

Little hints for new contributors:
* Please follow PSR and other good coding standards.

## Licensing

The code in this project is licensed under MIT license.
