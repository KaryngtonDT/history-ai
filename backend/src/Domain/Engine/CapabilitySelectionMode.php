<?php

declare(strict_types=1);

namespace App\Domain\Engine;

enum CapabilitySelectionMode: string
{
    case Auto = 'auto';
    case Manual = 'manual';
    case Locked = 'locked';

    public function label(): string
    {
        return match ($this) {
            self::Auto => 'Auto',
            self::Manual => 'Manual',
            self::Locked => 'Locked',
        };
    }
}
