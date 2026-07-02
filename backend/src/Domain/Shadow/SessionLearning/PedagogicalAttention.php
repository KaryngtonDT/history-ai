<?php

declare(strict_types=1);

namespace App\Domain\Shadow\SessionLearning;

enum PedagogicalAttention: string
{
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';
}
