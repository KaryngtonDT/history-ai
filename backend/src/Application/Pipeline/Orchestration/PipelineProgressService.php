<?php

declare(strict_types=1);

namespace App\Application\Pipeline\Orchestration;

use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineJob;
use App\Domain\PipelineJob\PipelineJobId;
use App\Domain\PipelineJob\PipelineJobRepositoryInterface;

class PipelineProgressService
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
        return $this->updateProgressDetailed(
            $jobId,
            $progressPercent,
            $currentStep,
            $estimatedRemainingSeconds,
        );
    }

    /**
     * @param array<string, mixed>|null $progressDetail
     */
    public function updateProgressDetailed(
        PipelineJobId $jobId,
        int $progressPercent,
        ?string $currentStep = null,
        ?int $estimatedRemainingSeconds = null,
        ?array $progressDetail = null,
    ): PipelineJob {
        $job = $this->jobRepository->findById($jobId);

        if (null === $job) {
            throw new \RuntimeException('Pipeline job not found.');
        }

        $updated = $job->updateProgress(
            $progressPercent,
            $currentStep,
            $estimatedRemainingSeconds,
            $progressDetail,
        );
        $this->jobRepository->save($updated);

        return $updated;
    }

    public function heartbeat(PipelineJobId $jobId, ?PipelineStageType $stage = null): PipelineJob
    {
        $job = $this->jobRepository->findById($jobId);

        if (null === $job) {
            throw new \RuntimeException('Pipeline job not found.');
        }

        $stage ??= $job->stage();
        $elapsed = null !== $job->startedAt()
            ? max(0, time() - $job->startedAt()->getTimestamp())
            : 0;
        $checkpoint = PipelineStageCheckpointRegistry::resolve($stage, $job->currentStep());
        $estimated = $job->estimatedDurationSeconds();
        $progress = $job->progressPercent();

        if (
            PipelineStageCheckpointRegistry::isProcessingCheckpoint($checkpoint['checkpoint'])
            && null !== $estimated
            && $estimated > 0
        ) {
            $ratio = min(1.0, $elapsed / $estimated);
            $progress = (int) round(
                $checkpoint['minPercent'] + (($checkpoint['maxPercent'] - $checkpoint['minPercent']) * $ratio),
            );
        }

        $remaining = null !== $estimated && $estimated > 0
            ? max(0, $estimated - $elapsed)
            : $job->estimatedRemainingSeconds();

        return $this->updateProgress($jobId, $progress, $job->currentStep(), $remaining);
    }
}
