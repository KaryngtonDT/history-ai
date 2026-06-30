<?php

declare(strict_types=1);

namespace App\Domain\Scheduler;

use App\Domain\Pipeline\PipelineStageType;
use App\Domain\Scheduler\Exception\InvalidExecutionScheduleException;

final readonly class ExecutionSchedule
{
    /**
     * @param list<ExecutionResource> $resources
     */
    public function __construct(
        private ExecutionScheduleId $id,
        private SchedulingStrategy $strategy,
        private ScheduledStageCollection $stages,
        private array $resources,
        private int $estimatedCompletionSeconds,
        private ?PipelineStageType $currentStage = null,
        private ?ResourceType $currentResource = null,
    ) {
        if ($this->estimatedCompletionSeconds < 1) {
            throw new InvalidExecutionScheduleException('Estimated completion must be at least 1 second.');
        }

        if ([] === $this->resources) {
            throw new InvalidExecutionScheduleException('Execution resources cannot be empty.');
        }

        $seen = [];

        foreach ($this->resources as $resource) {
            $key = $resource->type()->value;

            if (isset($seen[$key])) {
                throw new InvalidExecutionScheduleException(sprintf(
                    'Duplicate execution resource "%s".',
                    $key,
                ));
            }

            $seen[$key] = true;
        }
    }

    /**
     * @param list<ExecutionResource> $resources
     */
    public static function create(
        ExecutionScheduleId $id,
        SchedulingStrategy $strategy,
        ScheduledStageCollection $stages,
        array $resources,
        int $estimatedCompletionSeconds,
        ?PipelineStageType $currentStage = null,
        ?ResourceType $currentResource = null,
    ): self {
        return new self(
            $id,
            $strategy,
            $stages,
            array_values($resources),
            $estimatedCompletionSeconds,
            $currentStage,
            $currentResource,
        );
    }

    public function id(): ExecutionScheduleId
    {
        return $this->id;
    }

    public function strategy(): SchedulingStrategy
    {
        return $this->strategy;
    }

    public function stages(): ScheduledStageCollection
    {
        return $this->stages;
    }

    /**
     * @return list<ExecutionResource>
     */
    public function resources(): array
    {
        return $this->resources;
    }

    public function estimatedCompletionSeconds(): int
    {
        return $this->estimatedCompletionSeconds;
    }

    public function currentStage(): ?PipelineStageType
    {
        return $this->currentStage;
    }

    public function currentResource(): ?ResourceType
    {
        return $this->currentResource;
    }

    public function resourceFor(ResourceType $type): ?ExecutionResource
    {
        foreach ($this->resources as $resource) {
            if ($resource->type() === $type) {
                return $resource;
            }
        }

        return null;
    }

    public function withProgress(
        ?PipelineStageType $currentStage,
        ?ResourceType $currentResource,
        ScheduledStageCollection $stages,
        array $resources,
    ): self {
        return new self(
            $this->id,
            $this->strategy,
            $stages,
            $resources,
            $this->estimatedCompletionSeconds,
            $currentStage,
            $currentResource,
        );
    }
}
