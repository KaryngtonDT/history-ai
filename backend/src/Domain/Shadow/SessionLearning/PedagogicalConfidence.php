<?php

declare(strict_types=1);

namespace App\Domain\Shadow\SessionLearning;

enum PedagogicalConfidence: string
{
    case Growing = 'growing';
    case Stable = 'stable';
    case Struggling = 'struggling';
}
