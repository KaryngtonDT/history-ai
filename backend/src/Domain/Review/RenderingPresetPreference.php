<?php

declare(strict_types=1);

namespace App\Domain\Review;

enum RenderingPresetPreference: string
{
    case Quality = 'quality';
    case Balanced = 'balanced';
    case Speed = 'speed';
}
