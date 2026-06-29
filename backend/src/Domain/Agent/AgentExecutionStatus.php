<?php

declare(strict_types=1);

namespace App\Domain\Agent;

enum AgentExecutionStatus: string
{
    case Completed = 'completed';
    case Skipped = 'skipped';
    case Failed = 'failed';
}
