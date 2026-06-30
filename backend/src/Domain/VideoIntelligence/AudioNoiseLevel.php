<?php

declare(strict_types=1);

namespace App\Domain\VideoIntelligence;

enum AudioNoiseLevel: string
{
    case None = 'none';
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
}
