<?php

declare(strict_types=1);

namespace App\Domain\Runtime;

enum RuntimeCapabilityClassification: string
{
    case Core = 'core';
    case Optional = 'optional';
    case Premium = 'premium';
    case Experimental = 'experimental';
    case Deprecated = 'deprecated';

    public function label(): string
    {
        return match ($this) {
            self::Core => 'Core',
            self::Optional => 'Optional',
            self::Premium => 'Premium',
            self::Experimental => 'Experimental',
            self::Deprecated => 'Deprecated',
        };
    }
}
