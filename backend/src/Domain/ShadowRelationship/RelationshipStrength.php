<?php

declare(strict_types=1);

namespace App\Domain\ShadowRelationship;

enum RelationshipStrength: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case VeryHigh = 'very_high';

    public function score(): int
    {
        return match ($this) {
            self::Low => 1,
            self::Medium => 2,
            self::High => 3,
            self::VeryHigh => 4,
        };
    }
}
