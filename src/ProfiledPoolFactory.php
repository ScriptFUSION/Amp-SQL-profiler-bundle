<?php
declare(strict_types=1);

namespace ScriptFUSION\AmpSqlProfilerBundle;

use Amp\Sql\SqlConnectionPool;

final class ProfiledPoolFactory
{
    public function __invoke(ProfiledPool $pool): SqlConnectionPool
    {
        if (PHP_SAPI === 'cli') {
            return $pool->pool;
        }

        return $pool;
    }
}
