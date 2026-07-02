<?php

declare(strict_types=1);

namespace App\Domain\ShadowMemory;

enum MemoryImportance: string
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';
    case Critical = 'critical';

    public function score(): int
    {
        return match ($this) {
            self::Low => 1,
            self::Normal => 2,
            self::High => 3,
            self::Critical => 4,
        };
    }
}
