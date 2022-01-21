<?php
declare(strict_types=1);

namespace ScriptFUSION\Club250\SqlProfiler;

use Amp\Sql\Pool;

final class ProfiledPoolFactory
{
    public function __invoke(Pool $pool): Pool
    {
        if (PHP_SAPI === 'cli') {
            return $pool;
        }

        return new ProfiledPool($pool);
    }
}
