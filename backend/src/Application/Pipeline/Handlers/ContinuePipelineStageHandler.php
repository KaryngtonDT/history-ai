<?php

declare(strict_types=1);

namespace App\Application\Pipeline\Handlers;

use App\Application\Pipeline\Orchestration\PipelineOrchestrator;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineJobRepositoryInterface;

final class ContinuePipelineStageHandler
{
    public function __construct(
        private readonly PipelineJobRepositoryInterface $jobRepository,
        private readonly PipelineOrchestrator $orchestrator,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $sourceId, string $stage): array
    {
        $stageType = PipelineStageType::tryFrom($stage);

        if (null === $stageType) {
            throw new \InvalidArgumentException('Invalid pipeline stage.');
        }

        $jobs = $this->jobRepository->findBySourceId($sourceId);
        $job = null;

        foreach ($jobs as $candidate) {
            if ($candidate->stage() === $stageType) {
                $job = $candidate;
                break;
            }
        }

        if (null === $job) {
            throw new \RuntimeException('Pipeline job not found.');
        }

        $next = $this->orchestrator->continueToNextStage($job->jobId());

        return [
            'confirmedJob' => $this->orchestrator->serializeJob(
                $this->jobRepository->findById($job->jobId()) ?? $job,
            ),
            'nextJob' => null !== $next ? $this->orchestrator->serializeJob($next) : null,
        ];
    }
}
