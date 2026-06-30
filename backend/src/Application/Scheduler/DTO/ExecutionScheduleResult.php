<?php

declare(strict_types=1);

namespace App\Application\Scheduler\DTO;

use App\Domain\Scheduler\ExecutionSchedule;
use App\Domain\Scheduler\ExecutionResource;
use App\Domain\Scheduler\ScheduledStage;

final readonly class ExecutionScheduleResult
{
    /**
     * @param list<array{
     *     stage: string,
     *     order: int,
     *     status: string,
     *     estimatedDurationSeconds: int,
     *     parallelGroup: int,
     *     requirements: list<array{type: string, weight: int}>
     * }> $stages
     * @param list<array{
     *     type: string,
     *     running: int,
     *     pending: int,
     *     maxConcurrency: int
     * }> $resources
     */
    public function __construct(
        public string $id,
        public string $videoId,
        public string $strategy,
        public int $estimatedCompletionSeconds,
        public ?string $currentStage,
        public ?string $currentResource,
        public array $stages,
        public array $resources,
    ) {
    }

    public static function fromSchedule(string $videoId, ExecutionSchedule $schedule): self
    {
        $stages = array_map(
            static fn (ScheduledStage $stage): array => [
                'stage' => $stage->stage()->value,
                'order' => $stage->order(),
                'status' => $stage->status()->value,
                'estimatedDurationSeconds' => $stage->estimatedDurationSeconds(),
                'parallelGroup' => $stage->parallelGroup(),
                'requirements' => array_map(
                    static fn ($requirement): array => [
                        'type' => $requirement->type()->value,
                        'weight' => $requirement->weight(),
                    ],
                    $stage->requirements()->all(),
                ),
            ],
            $schedule->stages()->all(),
        );

        $resources = array_map(
            static fn (ExecutionResource $resource): array => [
                'type' => $resource->type()->value,
                'running' => $resource->running(),
                'pending' => $resource->pending(),
                'maxConcurrency' => $resource->maxConcurrency(),
            ],
            $schedule->resources(),
        );

        return new self(
            $schedule->id()->value,
            $videoId,
            $schedule->strategy()->value,
            $schedule->estimatedCompletionSeconds(),
            $schedule->currentStage()?->value,
            $schedule->currentResource()?->value,
            $stages,
            $resources,
        );
    }
}
