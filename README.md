# Vault PHP client ![Build Status](https://github.com/CSharpRU/vault-php/actions/workflows/tests.yml/badge.svg?branch=master) [![Codacy Badge](https://api.codacy.com/project/badge/Coverage/0bf9f46a659844658d847c1b2ab01e8b)](https://www.codacy.com/app/c_sharp/vault-php?utm_source=github.com&utm_medium=referral&utm_content=CSharpRU/vault-php&utm_campaign=Badge_Coverage) [![Codacy Badge](https://api.codacy.com/project/badge/Grade/0bf9f46a659844658d847c1b2ab01e8b)](https://www.codacy.com/app/c_sharp/vault-php?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=CSharpRU/vault-php&amp;utm_campaign=Badge_Grade) [![Read the Docs Badge](https://readthedocs.org/projects/vault-php/badge/?version=latest)](http://vault-php.readthedocs.io/en/latest/) 

![example workflow](https://github.com/CSharpRU/vault-php/actions/workflows/tests.yml/badge.svg?branch=master)

This is a PHP client for Vault - a tool for managing secrets.

## Features

* Supports different authentication backends with token caching and re-authentication.
* Different transports for different PHP versions.

## Installing / Getting started

Simply run this command within your directory with composer.json. 

```shell
composer require csharpru/vault-php
```

## Documentation

Latest documentation is available here: http://vault-php.readthedocs.io/en/latest/

## Developing

If you want to contribute, execute following shell commands:

```shell
git clone https://github.com/CSharpRU/vault-php.git
cd vault-php/
composer install
```

Now you're ready to write tests and code.

## Contributing

If you'd like to contribute, please fork the repository and use a feature
branch. Pull requests are warmly welcome.

Little hints for new contributors:
* This repository follows gitflow and semver.
* Please follow PSR and other good coding standards.

## Licensing

The code in this project is licensed under MIT license.
