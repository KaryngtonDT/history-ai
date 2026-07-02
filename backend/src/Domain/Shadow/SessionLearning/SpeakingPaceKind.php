<?php

declare(strict_types=1);

namespace App\Domain\Shadow\SessionLearning;

enum SpeakingPaceKind: string
{
    case Slow = 'slow';
    case Normal = 'normal';
    case Fast = 'fast';
}
