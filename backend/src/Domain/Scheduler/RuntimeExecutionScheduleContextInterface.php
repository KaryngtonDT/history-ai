<?php

declare(strict_types=1);

namespace App\Domain\Scheduler;

use App\Domain\Pipeline\PipelineStageType;

interface RuntimeExecutionScheduleContextInterface
{
    public function set(?ExecutionSchedule $schedule): void;

    public function get(): ?ExecutionSchedule;

    public function clear(): void;

    public function updateStage(PipelineStageType $stage, ScheduledStageStatus $status): void;
}
