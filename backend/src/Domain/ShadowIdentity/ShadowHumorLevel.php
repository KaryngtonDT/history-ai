<?php

declare(strict_types=1);

namespace App\Domain\ShadowIdentity;

enum ShadowHumorLevel: string
{
    case None = 'none';
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';

    public function scale(): int
    {
        return match ($this) {
            self::None => 0,
            self::Low => 3,
            self::Medium => 6,
            self::High => 9,
        };
    }

    public static function fromScale(int $scale): self
    {
        return match (true) {
            $scale <= 1 => self::None,
            $scale <= 4 => self::Low,
            $scale <= 7 => self::Medium,
            default => self::High,
        };
    }
}
