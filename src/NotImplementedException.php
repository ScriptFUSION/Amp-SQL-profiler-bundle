<?php
declare(strict_types=1);

namespace ScriptFUSION\AmpSqlProfilerBundle;

final class NotImplementedException extends \LogicException
{
    public function __construct()
    {
        parent::__construct('Method not implemented.');
    }
}
