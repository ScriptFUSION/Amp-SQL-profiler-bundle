<?php
declare(strict_types=1);

namespace ScriptFUSION\Club250\SqlProfiler;

use Amp\Promise;
use ScriptFUSION\StaticClass;
use function Amp\call;

final class AsyncTimer
{
    use StaticClass;

    public static function time(Promise $coroutine): Promise
    {
        return call(static function () use ($coroutine): \Generator {
            $start = microtime(true);

            $result = yield $coroutine;

            return [$result, microtime(true) - $start];
        });
    }
}
