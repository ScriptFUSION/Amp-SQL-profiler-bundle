<?php
declare(strict_types=1);

namespace ScriptFUSION\AmpSqlProfilerBundle;

use ScriptFUSION\StaticClass;

final class AsyncTimer
{
    use StaticClass;

    public static function time(\Closure $closure): array
    {
        $start = microtime(true);

        $result = $closure();

        return [$result, microtime(true) - $start];
    }
}
