<?php
declare(strict_types=1);

namespace ScriptFUSION\Club250\SqlProfiler;

use Amp\Promise;
use Amp\Sql\Transaction;
use function Amp\call;

final class ProfiledTransaction implements Transaction
{
    private array $queryBuffer = [];

    public function __construct(private array &$sql, private Transaction $transaction)
    {
    }

    public function query(string $sql): Promise
    {
        return call(function () use ($sql): \Generator {
            [$result, $time] = yield AsyncTimer::time($this->transaction->query($sql));

            $this->queryBuffer[] = new SqlQuery($sql, $time);

            return $result;
        });
    }

    public function execute(string $sql, array $params = []): Promise
    {
        return call(function () use ($sql, $params): \Generator {
            [$result, $time] = yield AsyncTimer::time($this->transaction->execute($sql, $params));

            $this->queryBuffer[] = new SqlQuery($sql, $time, $params);

            return $result;
        });
    }

    public function commit(): Promise
    {
        return call(function (): \Generator {
            [$result, $time] = yield AsyncTimer::time($this->transaction->commit());

            $this->sql = array_merge($this->sql, $this->queryBuffer, [new SqlQuery('COMMIT', $time)]);
            $this->queryBuffer = [];

            return $result;
        });
    }

    public function rollback(): Promise
    {
        $this->queryBuffer = [];

        return $this->transaction->rollback();
    }

    public function prepare(string $sql): Promise
    {
        // TODO: Implement prepare() method.
    }

    public function close()
    {
        return $this->transaction->close();
    }

    public function getIsolationLevel(): int
    {
        return $this->transaction->getIsolationLevel();
    }

    public function isActive(): bool
    {
        return $this->transaction->isActive();
    }

    public function createSavepoint(string $identifier): Promise
    {
        // TODO: Implement createSavepoint() method.
    }

    public function rollbackTo(string $identifier): Promise
    {
        // TODO: Implement rollbackTo() method.
    }

    public function releaseSavepoint(string $identifier): Promise
    {
        // TODO: Implement releaseSavepoint() method.
    }

    public function isAlive(): bool
    {
        return $this->transaction->isAlive();
    }

    public function getLastUsedAt(): int
    {
        return $this->transaction->getLastUsedAt();
    }
}
