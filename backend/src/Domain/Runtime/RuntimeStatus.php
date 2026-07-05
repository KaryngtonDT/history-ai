<?php

declare(strict_types=1);

namespace App\Domain\Runtime;

enum RuntimeStatus: string
{
    case Unknown = 'unknown';
    case Discovering = 'discovering';
    case Ready = 'ready';
    case Degraded = 'degraded';
    case Unavailable = 'unavailable';

    public function isOperational(): bool
    {
        return self::Ready === $this || self::Degraded === $this;
    }
}
