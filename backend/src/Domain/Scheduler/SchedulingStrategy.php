<?php

declare(strict_types=1);

namespace App\Domain\Scheduler;

enum SchedulingStrategy: string
{
    case Balanced = 'balanced';
    case Quality = 'quality';
    case Speed = 'speed';
    case LowMemory = 'low_memory';
}
