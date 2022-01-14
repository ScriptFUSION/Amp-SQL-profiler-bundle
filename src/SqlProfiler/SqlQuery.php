<?php
declare(strict_types=1);

namespace ScriptFUSION\Club250\SqlProfiler;

use Symfony\Component\VarDumper\Cloner\Data;

final class SqlQuery
{
    public function __construct(
        public string $sql,
        public float $executionMS,
        public array|Data $params = [],
    ) {
    }
}
