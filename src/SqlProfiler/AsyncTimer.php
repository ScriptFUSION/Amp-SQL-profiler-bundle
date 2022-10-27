<?php
declare(strict_types=1);

namespace ScriptFUSION\Club250\SqlProfiler;

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
