<?php

declare(strict_types=1);

namespace App\Domain\VideoIntelligence;

enum LightingCondition: string
{
    case Excellent = 'excellent';
    case Good = 'good';
    case Average = 'average';
    case Poor = 'poor';
}
