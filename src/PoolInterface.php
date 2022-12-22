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
     * Do not forget to release connection to the pool
     *
     * ```php
     * $pool->getConnection()
     *      ->then(function (ConnectionInterface $connection) use ($pool) {
     *          $connection->query("UPDATE users SET blocked = 1 WHERE id = 3");
     *
     *          $pool->releaseConnection($connection);
     *      })
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
}
