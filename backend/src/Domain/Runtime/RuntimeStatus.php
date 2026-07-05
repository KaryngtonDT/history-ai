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
    case Missing = 'missing';
    case Misconfigured = 'misconfigured';
    case Mock = 'mock';
    case Blocked = 'blocked';
    case Provisioning = 'provisioning';

    public function isOperational(): bool
    {
        return self::Ready === $this;
    }

    public function isReportedReady(): bool
    {
        return self::Ready === $this;
    }

    public function isTerminal(): bool
    {
        return self::Ready === $this || self::Blocked === $this;
    }
}
