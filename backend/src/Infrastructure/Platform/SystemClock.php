<?php

declare(strict_types=1);

namespace App\Infrastructure\Platform;

use App\Application\Platform\ClockInterface;

final class SystemClock implements ClockInterface
{
    public function now(): float
    {
        return microtime(true);
    }
}
