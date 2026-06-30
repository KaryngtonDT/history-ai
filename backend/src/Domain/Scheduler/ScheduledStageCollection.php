<?php

declare(strict_types=1);

namespace App\Domain\Scheduler;

use App\Domain\Pipeline\PipelineStageType;
use App\Domain\Scheduler\Exception\InvalidExecutionScheduleException;

final readonly class ScheduledStageCollection
{
    /** @var list<ScheduledStage> */
    private array $stages;

    /**
     * @param list<ScheduledStage> $stages
     */
    public function __construct(array $stages)
    {
        if ([] === $stages) {
            throw new InvalidExecutionScheduleException('Scheduled stages cannot be empty.');
        }

        $seen = [];
        $previousOrder = 0;

        foreach ($stages as $stage) {
            $key = $stage->stage()->value;

            if (isset($seen[$key])) {
                throw new InvalidExecutionScheduleException(sprintf(
                    'Duplicate scheduled stage "%s".',
                    $key,
                ));
            }

            if ($stage->order() <= $previousOrder) {
                throw new InvalidExecutionScheduleException('Scheduled stages must be ordered sequentially.');
            }

            $seen[$key] = true;
            $previousOrder = $stage->order();
        }

        $this->stages = array_values($stages);
    }

    /**
     * @return list<ScheduledStage>
     */
    public function all(): array
    {
        return $this->stages;
    }

    public function count(): int
    {
        return count($this->stages);
    }

    public function forStage(PipelineStageType $stage): ?ScheduledStage
    {
        foreach ($this->stages as $scheduledStage) {
            if ($scheduledStage->stage() === $stage) {
                return $scheduledStage;
            }
        }

        return null;
    }

    public function current(): ?ScheduledStage
    {
        foreach ($this->stages as $stage) {
            if (ScheduledStageStatus::Running === $stage->status()) {
                return $stage;
            }
        }

        return null;
    }

    public function withStatus(ScheduledStageStatus $status): self
    {
        return new self(array_map(
            static fn (ScheduledStage $stage): ScheduledStage => $stage->withStatus($status),
            $this->stages,
        ));
    }

    /**
     * @return list<ScheduledStage>
     */
    public function markStage(PipelineStageType $stageType, ScheduledStageStatus $status): self
    {
        $updated = [];

        foreach ($this->stages as $stage) {
            $updated[] = $stage->stage() === $stageType
                ? $stage->withStatus($status)
                : $stage;
        }

        return new self($updated);
    }
}
