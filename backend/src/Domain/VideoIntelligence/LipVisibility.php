<?php

declare(strict_types=1);

namespace App\Domain\VideoIntelligence;

enum LipVisibility: string
{
    case Excellent = 'excellent';
    case Good = 'good';
    case Partial = 'partial';
    case Poor = 'poor';
}
