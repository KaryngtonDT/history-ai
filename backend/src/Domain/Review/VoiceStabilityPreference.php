<?php

declare(strict_types=1);

namespace App\Domain\Review;

enum VoiceStabilityPreference: string
{
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';
}
