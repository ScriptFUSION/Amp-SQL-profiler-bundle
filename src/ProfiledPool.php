<?php
declare(strict_types=1);

namespace ScriptFUSION\AmpSqlProfilerBundle;

use Amp\Sql\Link;
use Amp\Sql\Pool;
use Amp\Sql\Result;
use Amp\Sql\Statement;
use Amp\Sql\Transaction;
use Amp\Sql\TransactionIsolation;
use Amp\Sql\TransactionIsolationLevel;

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

    public function query(string $sql): Result
    {
        [$result, $time] = AsyncTimer::time(fn () => $this->pool->query($sql));

        $this->sql[] = new SqlQuery($sql, $time);

        return $result;
    }

    public function execute(string $sql, array $params = []): Result
    {
        [$result, $time] = AsyncTimer::time(fn () => $this->pool->execute($sql, $params));

        $this->sql[] = new SqlQuery($sql, $time, $params);

        return $result;
    }

    public function beginTransaction(
        TransactionIsolation $isolation = TransactionIsolationLevel::Committed,
    ): Transaction {
        return new ProfiledTransaction($this->sql, $this->pool->beginTransaction($isolation));
    }

    public function prepare(string $sql): Statement
    {
        return $this->pool->prepare($sql);
    }

    public function close(): void
    {
        $this->pool->close();
    }

    public function isClosed(): bool
    {
        return $this->pool->isClosed();
    }

    public function onClose(\Closure $onClose): void
    {
        $this->pool->onClose($onClose);
    }

    public function extractConnection(): Link
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

    public function getLastUsedAt(): int
    {
        return $this->pool->getLastUsedAt();
    }
}
