<?php

declare(strict_types=1);

namespace App\Domain\ShadowExecutive;

enum ExecutivePriority: string
{
    case Critical = 'critical';
    case High = 'high';
    case Normal = 'normal';
    case Low = 'low';
}
