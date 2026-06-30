<?php

declare(strict_types=1);

namespace App\Domain\Orchestrator;

enum ProcessingStrategy: string
{
    case Balanced = 'balanced';
    case Quality = 'quality';
    case Speed = 'speed';
    case LowMemory = 'low_memory';
}
