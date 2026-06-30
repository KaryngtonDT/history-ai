<?php

declare(strict_types=1);

namespace App\Domain\Review;

enum LipSyncStrengthPreference: string
{
    case Strong = 'strong';
    case Moderate = 'moderate';
    case Subtle = 'subtle';
}
