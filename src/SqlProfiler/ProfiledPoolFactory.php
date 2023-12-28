<?php
declare(strict_types=1);

namespace ScriptFUSION\AmpSqlProfilerBundle;

use Amp\Sql\Pool;

final class ProfiledPoolFactory
{
    public function __invoke(ProfiledPool $pool): Pool
    {
        if (PHP_SAPI === 'cli') {
            return $pool->pool;
        }

        return $pool;
    }
}
