<?php

declare(strict_types=1);

namespace App\Domain\EngineAnalytics;

enum EngineExecutionStatus: string
{
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
}
