<?php
declare(strict_types=1);

namespace Nstwf\MysqlConnectionPool;

use Nstwf\MysqlConnection\ConnectionInterface;
use Nstwf\MysqlConnection\Factory\ConnectionFactory;
use Nstwf\MysqlConnection\Factory\ConnectionFactoryInterface;
use React\MySQL\Factory;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use SplObjectStorage;
use function React\Promise\reject;
use function React\Promise\resolve;


final class Pool implements PoolInterface
{
    private string $uri;
    private ConnectionFactoryInterface $factory;
    private bool $waitForConnections;
    private int $connectionLimit;
    private SplObjectStorage $free;
    private SplObjectStorage $occupied;
    private SplObjectStorage $all;
    private SplObjectStorage $queue;

    /**
     * @param  string  $uri  Uri
     * @param  ConnectionFactoryInterface|null  $factory
     * @param  int  $connectionLimit  0 - for unlimited connections, > 0 - exact count of connections
     * @param  bool  $waitForConnections  If set `false` - throw exception while connection limit reached on `getConnection` method
     */
    public function __construct(
        #[\SensitiveParameter]
        string $uri,
        ConnectionFactoryInterface $factory = null,
        int $connectionLimit = 10,
        bool $waitForConnections = true,
    ) {
        $this->uri = $uri;
        $this->factory = $factory ?? new ConnectionFactory(new Factory());
        $this->connectionLimit = $connectionLimit;
        $this->waitForConnections = $waitForConnections;

        $this->free = new SplObjectStorage();
        $this->occupied = new SplObjectStorage();
        $this->all = new SplObjectStorage();
        $this->queue = new SplObjectStorage();
    }

    public function getConnection(): PromiseInterface
    {
        if ($this->free->count() > 0) {
            //TODO: add selector strategy
            $this->free->rewind();

            $connection = $this->free->current();

            $this->free->detach($connection);
            $this->occupied->attach($connection);

            return resolve($connection);
        }

        if ($this->connectionLimit === 0
            || $this->all->count() < $this->connectionLimit
        ) {
            $connection = $this->createConnection();
            $this->occupied->attach($connection);
            $this->all->attach($connection);

            return resolve($connection);
        }

        if (!$this->waitForConnections) {
            return reject(new \Exception('No available connections'));
        }

        $deferred = new Deferred();

        $this->queue->attach($deferred);

        return $deferred->promise();
    }

    public function query(string $sql, array $params = []): PromiseInterface
    {
        return $this
            ->getConnection()
            ->then(function (ConnectionInterface $connection) use (
                $sql,
                $params
            ) {
                return $connection->query($sql, $params)
                    ->always(fn() => $this->releaseConnection($connection));
            });
    }

    public function transaction(callable $callable): PromiseInterface
    {
        return $this
            ->getConnection()
            ->then(function (ConnectionInterface $connection) use ($callable) {
                return $connection->transaction($callable)
                    ->always(fn() => $this->releaseConnection($connection));
            });
    }

    public function releaseConnection(ConnectionInterface $connection): void
    {
        if (!$this->occupied->contains($connection)) {
            return;
        }

        if ($this->queue->count() === 0) {
            $this->occupied->rewind();
            $this->occupied->detach($connection);

            $this->free->attach($connection);
        } else {
            $this->queue->rewind();

            /** @var Deferred $deferred */
            $deferred = $this->queue->current();

            $this->queue->detach($deferred);

            $deferred->resolve($connection);
        }
    }

    private function createConnection(): ConnectionInterface
    {
        return $this
            ->factory
            ->createConnection($this->uri);
    }
}
