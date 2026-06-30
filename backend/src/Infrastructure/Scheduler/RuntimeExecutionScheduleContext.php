<?php

declare(strict_types=1);

namespace App\Infrastructure\Scheduler;

use App\Domain\Pipeline\PipelineStageType;
use App\Domain\Scheduler\ExecutionResource;
use App\Domain\Scheduler\ExecutionSchedule;
use App\Domain\Scheduler\ResourceType;
use App\Domain\Scheduler\RuntimeExecutionScheduleContextInterface;
use App\Domain\Scheduler\ScheduledStage;
use App\Domain\Scheduler\ScheduledStageStatus;

final class RuntimeExecutionScheduleContext implements RuntimeExecutionScheduleContextInterface
{
    private ?ExecutionSchedule $schedule = null;

    public function set(?ExecutionSchedule $schedule): void
    {
        $this->schedule = $schedule;
    }

    public function get(): ?ExecutionSchedule
    {
        return $this->schedule;
    }

    public function clear(): void
    {
        $this->schedule = null;
    }

    public function updateStage(PipelineStageType $stage, ScheduledStageStatus $status): void
    {
        if (null === $this->schedule) {
            return;
        }

        $stages = $this->schedule->stages()->markStage($stage, $status);
        $resources = $this->recalculateResources($stages->all(), $this->schedule->resources());
        $currentStage = ScheduledStageStatus::Running === $status
            ? $stage
            : $this->schedule->currentStage();
        $currentResource = ScheduledStageStatus::Running === $status
            ? $stages->forStage($stage)?->primaryResource()
            : $this->schedule->currentResource();

        $this->schedule = $this->schedule->withProgress(
            $currentStage,
            $currentResource,
            $stages,
            $resources,
        );
    }

    /**
     * @param list<ScheduledStage> $stages
     * @param list<ExecutionResource> $resources
     * @return list<ExecutionResource>
     */
    private function recalculateResources(array $stages, array $resources): array
    {
        $updated = [];

        foreach ($resources as $resource) {
            $running = 0;
            $pending = 0;

            foreach ($stages as $stage) {
                if (!in_array($resource->type(), $stage->requirements()->types(), true)) {
                    continue;
                }

                if (ScheduledStageStatus::Running === $stage->status()) {
                    ++$running;
                }

                if (ScheduledStageStatus::Pending === $stage->status()) {
                    ++$pending;
                }
            }

            $updated[] = $resource->withCounts($running, $pending);
        }

        return $updated;
    }
}
