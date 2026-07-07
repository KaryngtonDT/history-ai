<?php

declare(strict_types=1);

namespace App\Application\Pipeline\Handlers;

use App\Application\Pipeline\Orchestration\PipelineInvalidationService;
use App\Application\Pipeline\Orchestration\PipelineOrchestrator;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineSourceType;

final class StartPipelineStageHandler
{
    public function __construct(
        private readonly PipelineOrchestrator $orchestrator,
        private readonly PipelineInvalidationService $invalidationService,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $sourceId, string $stage, bool $forceRestart = false): array
    {
        $stageType = PipelineStageType::tryFrom($stage);

        if (null === $stageType) {
            throw new \InvalidArgumentException('Invalid pipeline stage.');
        }

        $job = $this->orchestrator->getOrCreateJob(
            $sourceId,
            $stageType,
            PipelineSourceType::Video,
            $sourceId,
            forceRestart: $forceRestart,
        );

        if ($forceRestart) {
            $this->invalidationService->invalidateDependentStages($job);
        }

        $started = $this->orchestrator->startStage($job->jobId());

        return $this->orchestrator->serializeJob($started);
    }
}
