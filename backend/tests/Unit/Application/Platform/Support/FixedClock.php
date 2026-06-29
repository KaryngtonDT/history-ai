<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Platform\Support;

use App\Application\Platform\ClockInterface;

final class FixedClock implements ClockInterface
{
    public function __construct(
        private float $time = 0.0,
        private readonly float $step = 0.001,
    ) {
    }

    public function now(): float
    {
        $current = $this->time;
        $this->time += $this->step;

        return $current;
    }
}
