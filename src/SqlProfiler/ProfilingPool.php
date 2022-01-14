<?php
declare(strict_types=1);

namespace ScriptFUSION\Club250\SqlProfiler;

use Amp\Promise;
use Amp\Sql\Pool;
use Amp\Sql\Transaction;
use function Amp\call;

final class ProfilingPool implements Pool
{
    private array $sql = [];

    public function __construct(private Pool $pool)
    {
    }

    /**
     * @return SqlQuery[]
     */
    public function getSql(): array
    {
        return $this->sql;
    }

    public function query(string $sql): Promise
    {
        return call(function () use ($sql): \Generator {
            [$result, $time] = yield self::timeAsync($this->pool->query($sql));

            $this->sql[] = new SqlQuery($sql, $time);

            return $result;
        });
    }

    public function prepare(string $sql): Promise
    {
        return call(function () use ($sql): \Generator {
            [$result, $time] = yield self::timeAsync($this->pool->prepare($sql));

            $this->sql[] = new SqlQuery($sql, $time);

            return $result;
        });
    }

    public function execute(string $sql, array $params = []): Promise
    {
        return call(function () use ($sql, $params): \Generator {
            [$result, $time] = yield self::timeAsync($this->pool->execute($sql, $params));

            $this->sql[] = new SqlQuery($sql, $time, $params);

            return $result;
        });
    }

    private static function timeAsync(Promise $coroutine): Promise
    {
        return call(static function () use ($coroutine): \Generator {
            $start = microtime(true);

            $result = yield $coroutine;

            return [$result, microtime(true) - $start];
        });
    }

    public function close(): void
    {
        $this->pool->close();
    }

    public function beginTransaction(int $isolation = Transaction::ISOLATION_COMMITTED): Promise
    {
        return $this->pool->beginTransaction($isolation);
    }

    public function extractConnection(): Promise
    {
        return $this->pool->extractConnection();
    }

    public function getConnectionCount(): int
    {
        return $this->pool->getConnectionCount();
    }

    public function getIdleConnectionCount(): int
    {
        return $this->pool->getIdleConnectionCount();
    }

    public function getConnectionLimit(): int
    {
        return $this->pool->getConnectionLimit();
    }

    public function getIdleTimeout(): int
    {
        return $this->pool->getIdleTimeout();
    }

    public function isAlive(): bool
    {
        return $this->pool->isAlive();
    }

    public function getLastUsedAt(): int
    {
        return $this->pool->getLastUsedAt();
    }
}
