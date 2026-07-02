<?php

declare(strict_types=1);

namespace App\Domain\Shadow\SessionLearning;

enum PedagogicalPace: string
{
    case Fast = 'fast';
    case Normal = 'normal';
    case Slow = 'slow';
}
