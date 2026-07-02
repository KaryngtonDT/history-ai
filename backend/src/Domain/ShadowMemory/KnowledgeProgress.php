<?php

declare(strict_types=1);

namespace App\Domain\ShadowMemory;

enum KnowledgeProgress: string
{
    case New = 'new';
    case Learning = 'learning';
    case Practiced = 'practiced';
    case Mastered = 'mastered';

    public function percent(): int
    {
        return match ($this) {
            self::New => 10,
            self::Learning => 45,
            self::Practiced => 75,
            self::Mastered => 95,
        };
    }
}
