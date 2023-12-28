<?php
declare(strict_types=1);

namespace ScriptFUSION\AmpSqlProfilerBundle;

use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SqlDataCollector extends AbstractDataCollector
{
    public function __construct(private readonly ProfiledPool $profilingPool)
    {
    }

    public function collect(Request $request, Response $response, \Throwable $exception = null): void
    {
        foreach ($this->data['sql'] = $this->profilingPool->getSql() as $query) {
            $query->params = $this->cloneVar($query->params);
        }
    }

    public static function getTemplate(): ?string
    {
        return 'amp sql profiler/amp sql profiler';
    }

    /**
     * @return SqlQuery[]
     */
    public function getQueries(): array
    {
        return $this->data['sql'];
    }

    public function getQueryCount(): int
    {
        return count($this->getQueries());
    }

    public function getTime(): float
    {
        return array_reduce($this->getQueries(), fn (float $agg, SqlQuery $q) => $agg + $q->executionMS, 0);
    }
}
