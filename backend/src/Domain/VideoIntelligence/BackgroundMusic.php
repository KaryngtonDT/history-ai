<?php

declare(strict_types=1);

namespace App\Domain\VideoIntelligence;

enum BackgroundMusic: string
{
    case Detected = 'detected';
    case NotDetected = 'not_detected';
}
