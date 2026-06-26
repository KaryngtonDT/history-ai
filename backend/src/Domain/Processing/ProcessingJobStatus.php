<?php

declare(strict_types=1);

namespace App\Domain\Processing;

enum ProcessingJobStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
}
