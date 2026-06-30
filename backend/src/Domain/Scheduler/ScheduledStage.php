<?php

declare(strict_types=1);

namespace App\Domain\Scheduler;

use App\Domain\Pipeline\PipelineStageType;
use App\Domain\Scheduler\Exception\InvalidExecutionScheduleException;

final readonly class ScheduledStage
{
    public function __construct(
        private PipelineStageType $stage,
        private int $order,
        private ResourceRequirementCollection $requirements,
        private int $estimatedDurationSeconds,
        private int $parallelGroup,
        private ScheduledStageStatus $status = ScheduledStageStatus::Pending,
    ) {
        if ($this->order < 1) {
            throw new InvalidExecutionScheduleException('Scheduled stage order must be at least 1.');
        }

        if ($this->estimatedDurationSeconds < 1) {
            throw new InvalidExecutionScheduleException('Estimated duration must be at least 1 second.');
        }

        if ($this->parallelGroup < 1) {
            throw new InvalidExecutionScheduleException('Parallel group must be at least 1.');
        }
    }

    public static function create(
        PipelineStageType $stage,
        int $order,
        ResourceRequirementCollection $requirements,
        int $estimatedDurationSeconds,
        int $parallelGroup,
        ScheduledStageStatus $status = ScheduledStageStatus::Pending,
    ): self {
        return new self(
            $stage,
            $order,
            $requirements,
            $estimatedDurationSeconds,
            $parallelGroup,
            $status,
        );
    }

    public function stage(): PipelineStageType
    {
        return $this->stage;
    }

    public function order(): int
    {
        return $this->order;
    }

    public function requirements(): ResourceRequirementCollection
    {
        return $this->requirements;
    }

    public function estimatedDurationSeconds(): int
    {
        return $this->estimatedDurationSeconds;
    }

    public function parallelGroup(): int
    {
        return $this->parallelGroup;
    }

    public function status(): ScheduledStageStatus
    {
        return $this->status;
    }

    public function primaryResource(): ResourceType
    {
        return $this->requirements->primary()->type();
    }

    public function withStatus(ScheduledStageStatus $status): self
    {
        return new self(
            $this->stage,
            $this->order,
            $this->requirements,
            $this->estimatedDurationSeconds,
            $this->parallelGroup,
            $status,
        );
    }
}
