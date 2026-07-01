<?php

declare(strict_types=1);

namespace App\Domain\Shadow;

enum ShadowTutorMode: string
{
    case Off = 'off';
    case Gentle = 'gentle';
    case Normal = 'normal';

    public function isEnabled(): bool
    {
        return self::Off !== $this;
    }

    public function toPolicy(): ShadowInterventionPolicy
    {
        return match ($this) {
            self::Off => ShadowInterventionPolicy::disabled(),
            self::Gentle => ShadowInterventionPolicy::gentleDefault(),
            self::Normal => ShadowInterventionPolicy::normalDefault(),
        };
    }
}
