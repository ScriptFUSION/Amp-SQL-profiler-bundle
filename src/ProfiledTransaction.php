<?php
declare(strict_types=1);

namespace ScriptFUSION\AmpSqlProfilerBundle;

use Amp\Sql\Result;
use Amp\Sql\Statement;
use Amp\Sql\Transaction;
use Amp\Sql\TransactionIsolation;

final class ProfiledTransaction implements Transaction
{
    private array $queryBuffer = [];

    public function __construct(private array &$sql, private readonly Transaction $transaction)
    {
    }

    public function query(string $sql): Result
    {
        [$result, $time] = AsyncTimer::time($this->transaction->query($sql));

        $this->queryBuffer[] = new SqlQuery($sql, $time);

        return $result;
    }

    public function execute(string $sql, array $params = []): Result
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

    public function prepare(string $sql): Statement
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

    public function getIsolationLevel(): TransactionIsolation
    {
        return $this->transaction->getIsolationLevel();
    }

    public function isActive(): bool
    {
        return $this->transaction->isActive();
    }

    public function createSavepoint(string $identifier): void
    {
        // TODO: Implement createSavepoint() method.
        throw new NotImplementedException();
    }

    public function rollbackTo(string $identifier): void
    {
        // TODO: Implement rollbackTo() method.
        throw new NotImplementedException();
    }

    public function releaseSavepoint(string $identifier): void
    {
        // TODO: Implement releaseSavepoint() method.
        throw new NotImplementedException();
    }

    public function getLastUsedAt(): int
    {
        return $this->transaction->getLastUsedAt();
    }

    public function beginTransaction(): Transaction
    {
        return new self($this->sql, $this->transaction->beginTransaction());
    }

    public function getIsolation(): TransactionIsolation
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
