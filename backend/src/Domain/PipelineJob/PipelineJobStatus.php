<?php

declare(strict_types=1);

namespace App\Domain\PipelineJob;

enum PipelineJobStatus: string
{
    case Queued = 'queued';
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case WaitingUserConfirmation = 'waiting_user_confirmation';
    case WaitingUserChoice = 'waiting_user_choice';

    public function isActive(): bool
    {
        return match ($this) {
            self::Queued, self::Running => true,
            default => false,
        };
    }

    public function isWaitingForUser(): bool
    {
        return match ($this) {
            self::WaitingUserConfirmation, self::WaitingUserChoice => true,
            default => false,
        };
    }
}
