# Vault PHP client [![Build Status](https://travis-ci.org/CSharpRU/vault-php.svg?branch=master)](https://travis-ci.org/CSharpRU/vault-php) [![Codacy Badge](https://api.codacy.com/project/badge/Grade/0bf9f46a659844658d847c1b2ab01e8b)](https://www.codacy.com/app/c_sharp/vault-php?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=CSharpRU/vault-php&amp;utm_campaign=Badge_Grade)
> **Warning! This project is not production ready, however I'm using it inside a production project and it works fine.**
> **I could change versioning or break backward compatibility.**
> **Use it at your own risk.**

This is a PHP client for Vault - a tool for managing secrets.

## Installing / Getting started

Simply run this command within your directory with composer.json. 

```shell
composer require vault-php
```

## Documentation

TODO

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
