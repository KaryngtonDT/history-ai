<?php

declare(strict_types=1);

namespace App\Domain\Orchestrator;

enum ProcessingMode: string
{
    case Manual = 'manual';
    case Automatic = 'automatic';
}
