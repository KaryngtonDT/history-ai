<?php

declare(strict_types=1);

namespace App\Domain\Shadow;

enum ShadowChallengeLevel: string
{
    case Easy = 'easy';
    case Normal = 'normal';
    case Hard = 'hard';
}
