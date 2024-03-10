<?php
declare(strict_types=1);

namespace ScriptFUSION\AmpSqlProfilerBundle;

use Amp\Sql\SqlConfig;
use Amp\Sql\SqlConnection;
use Amp\Sql\SqlConnectionPool;
use Amp\Sql\SqlResult;
use Amp\Sql\SqlStatement;
use Amp\Sql\SqlTransaction;
use Amp\Sql\SqlTransactionIsolation;

final class ProfiledPool implements SqlConnectionPool
{
    /** @var SqlQuery[] A list of all queries executed on this pool, in execution order. */
    private array $sql = [];

    public function __construct(public SqlConnectionPool $pool)
    {
    }

    public function getSql(): array
    {
        return $this->sql;
    }

    public function query(string $sql): SqlResult
    {
        [$result, $time] = AsyncTimer::time(fn () => $this->pool->query($sql));

        $this->sql[] = new SqlQuery($sql, $time);

        return $result;
    }

    public function execute(string $sql, array $params = []): SqlResult
    {
        [$result, $time] = AsyncTimer::time(fn () => $this->pool->execute($sql, $params));

        $this->sql[] = new SqlQuery($sql, $time, $params);

        return $result;
    }

    public function beginTransaction(): SqlTransaction
    {
        return new ProfiledTransaction($this->sql, $this->pool->beginTransaction());
    }

    public function prepare(string $sql): SqlStatement
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

    public function extractConnection(): SqlConnection
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

    public function getConfig(): SqlConfig
    {
        return $this->pool->getConfig();
    }

    public function getTransactionIsolation(): SqlTransactionIsolation
    {
        return $this->pool->getTransactionIsolation();
    }

    public function setTransactionIsolation(SqlTransactionIsolation $isolation): void
    {
        $this->pool->setTransactionIsolation($isolation);
    }
}
