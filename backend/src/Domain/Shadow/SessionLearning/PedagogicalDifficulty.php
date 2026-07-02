<?php

declare(strict_types=1);

namespace App\Domain\Shadow\SessionLearning;

enum PedagogicalDifficulty: string
{
    case Easy = 'easy';
    case Intermediate = 'intermediate';
    case Advanced = 'advanced';
}
