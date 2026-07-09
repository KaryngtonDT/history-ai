<?php

declare(strict_types=1);

namespace App\Application\Pipeline\Orchestration;

use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\Exception\InvalidPipelineJobException;
use App\Domain\PipelineJob\PipelineJobRepositoryInterface;
use App\Domain\PipelineJob\PipelineJobStatus;
use App\Domain\PipelineJob\PipelineSourceType;

final class PipelineLegacyStageLauncher
{
    public function __construct(
        private readonly PipelineOrchestrator $orchestrator,
        private readonly PipelineJobRepositoryInterface $jobRepository,
        private readonly PipelineInvalidationService $invalidationService,
    ) {
    }

    /**
     * @param array<string, mixed> $stageMetadata
     *
     * @return array<string, mixed>
     */
    public function launch(
        string $sourceId,
        PipelineStageType $stage,
        array $stageMetadata = [],
        bool $forceRestart = false,
    ): array {
        $active = $this->jobRepository->findActiveBySourceAndStage($sourceId, $stage);

        if (
            null !== $active
            && !$forceRestart
            && in_array($active->status(), [
                PipelineJobStatus::Queued,
                PipelineJobStatus::Running,
                PipelineJobStatus::WaitingUserConfirmation,
            ], true)
        ) {
            throw new InvalidPipelineJobException(sprintf(
                'A %s pipeline job is already active.',
                $stage->value,
            ));
        }

        $job = $this->orchestrator->getOrCreateJob(
            $sourceId,
            $stage,
            PipelineSourceType::Video,
            $sourceId,
            is_string($stageMetadata['provider'] ?? null) ? (string) $stageMetadata['provider'] : null,
            forceRestart: $forceRestart,
        );

        if ([] !== $stageMetadata) {
            $job = $job->withStageMetadata($stageMetadata);
            $this->jobRepository->save($job);
        }

        if ($forceRestart) {
            $this->invalidationService->invalidateDependentStages($job);
        }

        $started = $this->orchestrator->startStage($job->jobId());

        return $this->orchestrator->serializeJob($started);
    }
}
