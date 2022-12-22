# ReactPHP MySQL connection pool

[![CI](https://img.shields.io/github/actions/workflow/status/nstwfdev/mysql-connection-pool/ci.yml?branch=master&label=ci&logo=github)](https://github.com/nstwfdev/mysql-connection-pool/actions?query=workflow%3Aci+branch%3Amaster)
[![codecov](https://codecov.io/gh/nstwfdev/mysql-connection-pool/branch/master/graph/badge.svg?token=9YL9FSM4RV)](https://codecov.io/gh/nstwfdev/mysql-connection-pool)
[![Packagist Version](https://img.shields.io/packagist/v/nstwf/mysql-connection-pool?logo=packagist)](https://packagist.org/packages/nstwf/mysql-connection-pool)

Simple connection pool for [mysql client](https://github.com/friends-of-reactphp/mysql) using [connection](https://github.com/nstwfdev/mysql-connection)

**Table of contents**

* [Quickstart example](#quickstart-example)
* [Usage](#usage)
    * [PoolInterface](#poolinterface)
        * [Options](#options)
        * [getConnection()](#getconnection)
        * [releaseConnection()](#releaseconnection)
* [Install](#install)
* [Tests](#tests)
* [License](#license)

## Quickstart example

```php
$pool = new \Nstwf\MysqlConnectionPool\Pool('localhost:3306');

$pool
    ->getConnection()
    ->then(function (ConnectionInterface $connection) use ($pool) {
        $connection->query("UPDATE users SET blocked = 1 WHERE id = 3");
        
        $pool->releaseConnection($connection);
    });
```

## Usage

### PoolInterface

The main role of `PoolInterface` - managing connections with selected options

#### Options

- `waitForConnections: bool`, set up the behavior while no free connections exists and user call `getConnection` method. If set to `false` - throws an exception, otherwise return a promise with free connection. (Default: `true`)
- `connectionLimit: int`, the maximum number of connections at the same time. `0` - for unlimited. (Default: `5`)

```php
$pool = new \Nstwf\MysqlConnectionPool\Pool('localhost:3306', null, 10, false);
```

#### getConnection

The `getConnection(): PromiseInterface<ConnectionInterface>` method can be used to create a new `ConnectionInterface` instance if no free connections available, otherwise select one of free

```php
$pool->getConnection()
     ->then(function (ConnectionInterface $connection) {
        $connection->query("UPDATE users SET active = 0 WHERE id = 2");
        $connection->query("UPDATE users SET blocked = 1 WHERE id = 3");
        
        $pool->releaseConnection($connection);
     });
```

#### releaseConnection

The `releaseConnection(ConnectionInterface $connection): void` method can be used to release connection to the pool

```php
$pool->releaseConnection($connection);
```

## Install

The recommended way to install this library is [through Composer](https://getcomposer.org).
[New to Composer?](https://getcomposer.org/doc/00-intro.md)

This project follows [SemVer](https://semver.org/).
This will install the latest supported version:

```bash
composer require nstwf/mysql-connection
```

See also the [CHANGELOG](docs/CHANGELOG.md) for details about version upgrades.

It's *highly recommended to use PHP 8+* * for this project.

## Tests

To run the test suite, you first need to clone this repo and then install all
dependencies [through Composer](https://getcomposer.org):

```bash
composer install
```

To run the test suite, go to the project root and run:

```bash
vendor/bin/phpunit
```

## License

MIT, see [LICENSE file](LICENSE).

- [mysqljs/mysql](https://github.com/mysqljs/mysql) - main concept
