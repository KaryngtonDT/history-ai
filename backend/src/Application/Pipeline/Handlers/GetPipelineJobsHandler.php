<?php

declare(strict_types=1);

namespace App\Application\Pipeline\Handlers;

use App\Application\Pipeline\Orchestration\PipelineOrchestrator;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineJob;
use App\Domain\PipelineJob\PipelineJobRepositoryInterface;

final class GetPipelineJobsHandler
{
    public function __construct(
        private readonly PipelineOrchestrator $orchestrator,
        private readonly PipelineJobRepositoryInterface $jobRepository,
    ) {
    }

    /** @return array<string, mixed> */
    public function forSource(string $sourceId): array
    {
        return $this->orchestrator->buildSourceStatus($sourceId);
    }

    /** @return array<string, mixed>|null */
    public function forSourceStage(string $sourceId, string $stage): ?array
    {
        $stageType = PipelineStageType::tryFrom($stage);

        if (null === $stageType) {
            return null;
        }

        $job = $this->jobRepository->findActiveBySourceAndStage($sourceId, $stageType)
            ?? $this->findLatestByStage($sourceId, $stageType);

        return null !== $job ? $this->orchestrator->serializeJob($job) : null;
    }

    private function findLatestByStage(string $sourceId, PipelineStageType $stage): ?PipelineJob
    {
        foreach ($this->jobRepository->findBySourceId($sourceId) as $job) {
            if ($job->stage() === $stage) {
                return $job;
            }
        }

        return null;
    }
}
