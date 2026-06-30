<?php

declare(strict_types=1);

namespace App\Domain\Workspace;

enum BatchJobStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Completed = 'completed';
    case PartialFailure = 'partial_failure';
    case Failed = 'failed';
}
