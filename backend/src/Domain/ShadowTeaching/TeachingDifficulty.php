<?php

declare(strict_types=1);

namespace App\Domain\ShadowTeaching;

enum TeachingDifficulty: string
{
    case Easy = 'easy';
    case Normal = 'normal';
    case Advanced = 'advanced';
}
