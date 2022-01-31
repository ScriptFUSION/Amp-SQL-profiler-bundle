<?php
declare(strict_types=1);

namespace ScriptFUSION\Club250\SqlProfiler;

use Symfony\Component\VarDumper\Cloner\Data;

final class SqlQuery
{
    public array $backtrace;

    public function __construct(
        public string $sql,
        public float $executionMS,
        public array|Data $params = [],
    ) {
        $this->backtrace = array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 1);
    }
}
