<?php


namespace Nstwf\MysqlConnectionPool;


use Nstwf\MysqlConnection\ConnectionInterface;
use Nstwf\MysqlConnection\Factory\ConnectionFactoryInterface;
use Nstwf\MysqlConnectionPool\Exception\NoAvailableConnectionsException;
use PHPUnit\Framework\TestCase;
use React\MySQL\QueryResult;
use React\Promise\PromiseInterface;
use function React\Async\await;
use function React\Promise\resolve;


class PoolTest extends TestCase
{
    public function testGetConnectionWithoutFreeAndOccupiedWillReturnNewConnection(
    ): void
    {
        $factory = $this
            ->getMockBuilder(ConnectionFactoryInterface::class)
            ->getMock();

        $factory
            ->expects($this->once())
            ->method('createConnection')
            ->with('localhost:3306')
            ->willReturn($this->getMockBuilder(ConnectionInterface::class)
                ->getMock());

        $pool = new Pool('localhost:3306', $factory);
        $pool->getConnection();
    }

    public function testGetConnectionTwiceWillReturnDifferentConnection(): void
    {
        $connection1 = $this->getMockBuilder(ConnectionInterface::class)
            ->getMock();
        $connection2 = $this->getMockBuilder(ConnectionInterface::class)
            ->getMock();

        $factory = $this
            ->getMockBuilder(ConnectionFactoryInterface::class)
            ->getMock();

        $factory
            ->method('createConnection')
            ->willReturnOnConsecutiveCalls(
                $connection1,
                $connection2
            );

        $pool = new Pool('localhost:3306', $factory);

        $this->assertEquals($connection1, await($pool->getConnection()));
        $this->assertEquals($connection2, await($pool->getConnection()));
    }

    public function testGetConnectionAfterReleasePreviousWillReturnTheSameConnection(
    ): void
    {
        $expectedConnection = $this->getMockBuilder(ConnectionInterface::class)
            ->getMock();

        $factory = $this
            ->getMockBuilder(ConnectionFactoryInterface::class)
            ->getMock();

        $factory
            ->expects($this->once())
            ->method('createConnection')
            ->with('localhost:3306')
            ->willReturn($expectedConnection);

        $pool = new Pool('localhost:3306', $factory);

        $connection1 = await($pool->getConnection());
        $this->assertEquals($expectedConnection, $connection1);

        $pool->releaseConnection($connection1);

        $connection2 = await($pool->getConnection());
        $this->assertEquals($expectedConnection, $connection2);
    }

    public function testGetConnectionWithConnectionLimitWhileNoFreeConnectionsWillWaitPreviousConnectionRelease(
    ): void
    {
        $expectedConnection = $this->getMockBuilder(ConnectionInterface::class)
            ->getMock();

        $factory = $this
            ->getMockBuilder(ConnectionFactoryInterface::class)
            ->getMock();

        $factory
            ->expects($this->exactly(1))
            ->method('createConnection')
            ->with('localhost:3306')
            ->willReturn($expectedConnection);

        $pool = new Pool('localhost:3306', $factory, 1);

        $connection = await($pool->getConnection());
        $this->assertEquals($expectedConnection, $connection);

        $this->assertPromise($pool->getConnection(), $connection,
            fn() => $pool->releaseConnection($connection));
    }

    public function testGetConnectionQueueWithConnectionLimitWhileNoFreeConnectionsWillReturnInRightOrder(
    ): void
    {
        $expectedConnection1 = $this->getMockBuilder(ConnectionInterface::class)
            ->getMock();
        $expectedConnection2 = $this->getMockBuilder(ConnectionInterface::class)
            ->getMock();
        $expectedConnection3 = $this->getMockBuilder(ConnectionInterface::class)
            ->getMock();

        $factory = $this
            ->getMockBuilder(ConnectionFactoryInterface::class)
            ->getMock();

        $factory
            ->expects($this->exactly(3))
            ->method('createConnection')
            ->with('localhost:3306')
            ->willReturnOnConsecutiveCalls(
                $expectedConnection1,
                $expectedConnection2,
                $expectedConnection3,
            );

        $pool = new Pool('localhost:3306', $factory, 3);

        $connection1 = await($pool->getConnection());
        $connection2 = await($pool->getConnection());
        $connection3 = await($pool->getConnection());

        $queueConnection1 = $pool->getConnection();
        $queueConnection2 = $pool->getConnection();
        $queueConnection3 = $pool->getConnection();
        $queueConnection4 = $pool->getConnection();

        $pool->releaseConnection($connection3);
        $this->assertEquals($expectedConnection3, await($queueConnection1));

        $pool->releaseConnection($connection2);
        $this->assertEquals($expectedConnection2, await($queueConnection2));

        $pool->releaseConnection($connection1);
        $this->assertEquals($expectedConnection1, await($queueConnection3));

        $pool->releaseConnection($connection1);
        $this->assertEquals($expectedConnection1, await($queueConnection4));
    }

    public function testGetConnectionWithConnectionLimitAndNotWaitConnectionWillThrowException(
    ): void
    {
        $factory = $this
            ->getMockBuilder(ConnectionFactoryInterface::class)
            ->getMock();

        $factory
            ->expects($this->exactly(1))
            ->method('createConnection')
            ->with('localhost:3306')
            ->willReturn($this->getMockBuilder(ConnectionInterface::class)
                ->getMock());

        $pool = new Pool('localhost:3306', $factory, 1, false);

        $connection1 = await($pool->getConnection());

        $this->expectException(NoAvailableConnectionsException::class);
        $connection2 = await($pool->getConnection());
    }

    public function testReleaseConnectionTwiceWillDoNothing(): void
    {
        $factory = $this
            ->getMockBuilder(ConnectionFactoryInterface::class)
            ->getMock();

        $factory
            ->expects($this->exactly(1))
            ->method('createConnection')
            ->with('localhost:3306')
            ->willReturn($this->getMockBuilder(ConnectionInterface::class)
                ->getMock());

        $pool = new Pool('localhost:3306', $factory, 1, false);

        $connection = await($pool->getConnection());
        $pool->releaseConnection($connection);
        $pool->releaseConnection($connection);
    }

    public function testQuery(): void
    {
        $connection = $this
            ->getMockBuilder(ConnectionInterface::class)
            ->getMock();

        $connection
            ->expects($this->once())
            ->method('query')
            ->with('UPDATE users SET active = 0 WHERE id = 2')
            ->willReturn(resolve(new QueryResult()));

        $factory = $this
            ->getMockBuilder(ConnectionFactoryInterface::class)
            ->getMock();

        $factory
            ->expects($this->exactly(1))
            ->method('createConnection')
            ->with('localhost:3306')
            ->willReturn($connection);

        $pool = new Pool('localhost:3306', $factory);
        $queryResult
            = await($pool->query('UPDATE users SET active = 0 WHERE id = 2'));

        $this->assertEquals(new QueryResult(), $queryResult);
    }

    public function testTransaction(): void
    {
        $connection = $this
            ->getMockBuilder(ConnectionInterface::class)
            ->getMock();

        $connection
            ->expects($this->once())
            ->method('transaction')
            ->willReturn(resolve(null));

        $factory = $this
            ->getMockBuilder(ConnectionFactoryInterface::class)
            ->getMock();

        $factory
            ->expects($this->exactly(1))
            ->method('createConnection')
            ->with('localhost:3306')
            ->willReturn($connection);

        $pool = new Pool('localhost:3306', $factory);

        $queryResult = await(
            $pool->transaction(
                fn(ConnectionInterface $connection
                ) => $connection->query('UPDATE users SET active = 0 WHERE id = 2')
            )
        );

        $this->assertEquals(null, $queryResult);
    }

    private function assertPromise(
        PromiseInterface $promise,
        mixed $expectedPromiseValue,
        callable $callable
    ): void {
        $callable();

        $this->assertEquals($expectedPromiseValue, await($promise));
    }
}
