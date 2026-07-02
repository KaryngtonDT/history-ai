<?php

declare(strict_types=1);

namespace App\Domain\ShadowExecutive;

enum DecisionImpact: string
{
    case Knowledge = 'knowledge';
    case Goal = 'goal';
    case Time = 'time';
    case Difficulty = 'difficulty';
    case Confidence = 'confidence';
}
