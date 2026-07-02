<?php

declare(strict_types=1);

namespace App\Domain\Shadow\SessionLearning;

enum PedagogicalFatigue: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
}
