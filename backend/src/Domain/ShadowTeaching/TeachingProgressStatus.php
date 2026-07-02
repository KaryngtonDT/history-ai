<?php

declare(strict_types=1);

namespace App\Domain\ShadowTeaching;

enum TeachingProgressStatus: string
{
    case NotStarted = 'not_started';
    case Learning = 'learning';
    case Practicing = 'practicing';
    case Mastered = 'mastered';
    case ReviewNeeded = 'review_needed';

    public function percent(): int
    {
        return match ($this) {
            self::NotStarted => 0,
            self::Learning => 35,
            self::Practicing => 65,
            self::Mastered => 95,
            self::ReviewNeeded => 50,
        };
    }
}
