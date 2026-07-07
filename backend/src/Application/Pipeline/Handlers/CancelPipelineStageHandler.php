<?php

declare(strict_types=1);

namespace App\Application\Pipeline\Handlers;

use App\Application\Pipeline\Orchestration\PipelineCancellationService;
use App\Application\Pipeline\Orchestration\PipelineOrchestrator;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineJobRepositoryInterface;

final class CancelPipelineStageHandler
{
    public function __construct(
        private readonly PipelineJobRepositoryInterface $jobRepository,
        private readonly PipelineCancellationService $cancellationService,
        private readonly PipelineOrchestrator $orchestrator,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $sourceId, string $stage, string $reason = 'Cancelled by user'): array
    {
        $stageType = PipelineStageType::tryFrom($stage);

        if (null === $stageType) {
            throw new \InvalidArgumentException('Invalid pipeline stage.');
        }

        $job = $this->jobRepository->findActiveBySourceAndStage($sourceId, $stageType);

        if (null === $job) {
            throw new \RuntimeException('No active pipeline job for this stage.');
        }

        $cancelled = $this->cancellationService->cancel($job->jobId(), $reason);

        return $this->orchestrator->serializeJob($cancelled);
    }
}
