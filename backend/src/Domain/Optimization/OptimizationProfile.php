<?php

declare(strict_types=1);

namespace App\Domain\Optimization;

enum OptimizationProfile: string
{
    case Balanced = 'balanced';
    case Quality = 'quality';
    case Speed = 'speed';
    case LowMemory = 'low_memory';
}
