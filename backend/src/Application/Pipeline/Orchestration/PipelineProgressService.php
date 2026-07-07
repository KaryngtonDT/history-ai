<?php

declare(strict_types=1);

namespace App\Application\Pipeline\Orchestration;

use App\Domain\PipelineJob\PipelineJob;
use App\Domain\PipelineJob\PipelineJobId;
use App\Domain\PipelineJob\PipelineJobRepositoryInterface;

final class PipelineProgressService
{
    public function __construct(
        private readonly PipelineJobRepositoryInterface $jobRepository,
    ) {
    }

    public function updateProgress(
        PipelineJobId $jobId,
        int $progressPercent,
        ?string $currentStep = null,
        ?int $estimatedRemainingSeconds = null,
    ): PipelineJob {
        $job = $this->jobRepository->findById($jobId);

        if (null === $job) {
            throw new \RuntimeException('Pipeline job not found.');
        }

        $updated = $job->updateProgress($progressPercent, $currentStep, $estimatedRemainingSeconds);
        $this->jobRepository->save($updated);

        return $updated;
    }

    public function heartbeat(PipelineJobId $jobId): PipelineJob
    {
        $job = $this->jobRepository->findById($jobId);

        if (null === $job) {
            throw new \RuntimeException('Pipeline job not found.');
        }

        $elapsed = null !== $job->startedAt()
            ? max(0, time() - $job->startedAt()->getTimestamp())
            : 0;
        $remaining = $job->estimatedDurationSeconds() !== null
            ? max(0, $job->estimatedDurationSeconds() - $elapsed)
            : null;
        $progress = $job->estimatedDurationSeconds() !== null && $job->estimatedDurationSeconds() > 0
            ? min(95, (int) round(($elapsed / $job->estimatedDurationSeconds()) * 100))
            : $job->progressPercent();

        return $this->updateProgress($jobId, $progress, $job->currentStep(), $remaining);
    }
}
