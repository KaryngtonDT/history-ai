<?php

declare(strict_types=1);

namespace App\Domain\VideoIntelligence;

enum SpeechSpeed: string
{
    case Slow = 'slow';
    case Normal = 'normal';
    case Fast = 'fast';
}
