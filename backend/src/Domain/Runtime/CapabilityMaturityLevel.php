<?php

declare(strict_types=1);

namespace App\Domain\Runtime;

enum CapabilityMaturityLevel: string
{
    case Stable = 'stable';
    case Beta = 'beta';
    case Experimental = 'experimental';
    case Legacy = 'legacy';
    case Planned = 'planned';

    public function label(): string
    {
        return match ($this) {
            self::Stable => 'Stable',
            self::Beta => 'Beta',
            self::Experimental => 'Experimental',
            self::Legacy => 'Legacy',
            self::Planned => 'Planned',
        };
    }
}
