<?php


declare(strict_types=1);


namespace Nstwf\MysqlConnectionPool;


use Nstwf\MysqlConnection\ConnectionInterface;
use React\Promise\PromiseInterface;


interface PoolInterface
{
    /**
     * Return connection and occupy it
     *
     * **Do not forget to release connection to the pool**
     *
     * ```php
     * $pool->getConnection()
     *      ->then(function (ConnectionInterface $connection) use ($pool) {
     *          $connection->query("UPDATE users SET blocked = 1 WHERE id = 3");
     *
     *          $pool->releaseConnection($connection);
     *      });
     * ```
     *
     * @return PromiseInterface<ConnectionInterface>
     */
    public function getConnection(): PromiseInterface;

    /**
     * Release connection to the pool
     *
     * ```php
     * $pool->releaseConnection($connection);
     * ```
     *
     * @param ConnectionInterface $connection
     *
     * @return void
     */
    public function releaseConnection(ConnectionInterface $connection): void;

    /**
     * Shortcut for calls `getConnection()` -> `query()` -> `releaseConnection()`
     *
     * ```php
     * $pool->query("UPDATE users SET active = 0 WHERE id = ?", [2]);
     * ```
     *
     * @param string $sql
     * @param array  $params
     *
     * @return PromiseInterface
     */
    public function query(string $sql, array $params = []): PromiseInterface;

    /**
     * Shortcut for calls `getConnection()` -> `transaction()` -> `releaseConnection()`
     *
     * ```php
     * $pool->query("UPDATE users SET active = 0 WHERE id = ?", [2]);
     * ```
     *
     * @param callable $callable
     *
     * @return PromiseInterface
     */
    public function transaction(callable $callable): PromiseInterface;

}
