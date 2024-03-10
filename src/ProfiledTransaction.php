<?php
declare(strict_types=1);

namespace ScriptFUSION\AmpSqlProfilerBundle;

use Amp\Sql\SqlResult;
use Amp\Sql\SqlStatement;
use Amp\Sql\SqlTransaction;
use Amp\Sql\SqlTransactionIsolation;

final class ProfiledTransaction implements SqlTransaction
{
    private array $queryBuffer = [];

    public function __construct(private array &$sql, private readonly SqlTransaction $transaction)
    {
    }

    public function query(string $sql): SqlResult
    {
        [$result, $time] = AsyncTimer::time($this->transaction->query($sql));

        $this->queryBuffer[] = new SqlQuery($sql, $time);

        return $result;
    }

    public function execute(string $sql, array $params = []): SqlResult
    {
        [$result, $time] = AsyncTimer::time(fn () => $this->transaction->execute($sql, $params));

        $this->queryBuffer[] = new SqlQuery($sql, $time, $params);

        return $result;
    }

    public function commit(): void
    {
        [1 => $time] = AsyncTimer::time(fn () => $this->transaction->commit());

        $this->sql = array_merge($this->sql, $this->queryBuffer, [new SqlQuery('COMMIT', $time)]);
        $this->queryBuffer = [];
    }

    public function rollback(): void
    {
        $this->queryBuffer = [];

        $this->transaction->rollback();
    }

    public function prepare(string $sql): SqlStatement
    {
        // TODO: Implement prepare() method.
        throw new NotImplementedException();
    }

    public function close(): void
    {
        $this->transaction->close();
    }

    public function isClosed(): bool
    {
        return $this->transaction->isClosed();
    }

    public function onClose(\Closure $onClose): void
    {
        $this->transaction->onClose($onClose);
    }

    public function isActive(): bool
    {
        return $this->transaction->isActive();
    }

    public function getLastUsedAt(): int
    {
        return $this->transaction->getLastUsedAt();
    }

    public function beginTransaction(): SqlTransaction
    {
        return new self($this->sql, $this->transaction->beginTransaction());
    }

    public function getIsolation(): SqlTransactionIsolation
    {
        return $this->transaction->getIsolation();
    }

    public function getSavepointIdentifier(): ?string
    {
        return $this->transaction->getSavepointIdentifier();
    }

    public function onCommit(\Closure $onCommit): void
    {
        $this->transaction->onCommit($onCommit);
    }

    public function onRollback(\Closure $onRollback): void
    {
        $this->transaction->onRollback($onRollback);
    }
}
