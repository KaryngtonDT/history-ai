<?php

declare(strict_types=1);

namespace App\Domain\Engine;

enum EngineCatalogTier: string
{
    case Default = 'default';
    case CpuAlternative = 'cpu_alternative';
    case Lightweight = 'lightweight';
    case PremiumNvidia = 'premium_nvidia';
    case Experimental = 'experimental';
    case Legacy = 'legacy';
    case Alternative = 'alternative';

    public function label(): string
    {
        return match ($this) {
            self::Default => 'Default',
            self::CpuAlternative => 'CPU Alternative',
            self::Lightweight => 'Lightweight',
            self::PremiumNvidia => 'NVIDIA Premium',
            self::Experimental => 'Experimental',
            self::Legacy => 'Legacy',
            self::Alternative => 'Alternative',
        };
    }
}
