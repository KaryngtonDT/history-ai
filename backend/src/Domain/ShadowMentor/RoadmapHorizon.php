<?php

declare(strict_types=1);

namespace App\Domain\ShadowMentor;

enum RoadmapHorizon: string
{
    case Today = 'today';
    case Week = 'week';
    case Month = 'month';
    case Quarter = 'quarter';
    case Goal = 'goal';
}
