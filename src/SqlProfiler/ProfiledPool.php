<?php
declare(strict_types=1);

namespace ScriptFUSION\Club250\SqlProfiler;

use Amp\Promise;
use Amp\Sql\Pool;
use Amp\Sql\Transaction;
use function Amp\call;

final class ProfiledPool implements Pool
{
    /** @var SqlQuery[] A list of all queries executed on this pool, in execution order. */
    private array $sql = [];

    public function __construct(public Pool $pool)
    {
    }

    public function getSql(): array
    {
        return $this->sql;
    }

    public function query(string $sql): Promise
    {
        return call(function () use ($sql): \Generator {
            [$result, $time] = yield AsyncTimer::time($this->pool->query($sql));

            $this->sql[] = new SqlQuery($sql, $time);

            return $result;
        });
    }

    public function execute(string $sql, array $params = []): Promise
    {
        return call(function () use ($sql, $params): \Generator {
            [$result, $time] = yield AsyncTimer::time($this->pool->execute($sql, $params));

            $this->sql[] = new SqlQuery($sql, $time, $params);

            return $result;
        });
    }

    /**
     * @return Promise<Transaction>
     */
    public function beginTransaction(int $isolation = Transaction::ISOLATION_COMMITTED): Promise
    {
        return call(fn () => new ProfiledTransaction($this->sql, yield $this->pool->beginTransaction($isolation)));
    }

    public function prepare(string $sql): Promise
    {
        return $this->pool->prepare($sql);
    }

    public function close(): void
    {
        $this->pool->close();
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
