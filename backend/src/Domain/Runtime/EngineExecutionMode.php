<?php

declare(strict_types=1);

namespace App\Domain\Runtime;

enum EngineExecutionMode: string
{
    case Real = 'real';
    case Mock = 'mock';
    case Shim = 'shim';

    public function isReal(): bool
    {
        return self::Real === $this;
    }
}
