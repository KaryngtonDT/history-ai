<?php

declare(strict_types=1);

namespace App\Domain\ShadowMemory;

enum MemoryConfidence: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
}
