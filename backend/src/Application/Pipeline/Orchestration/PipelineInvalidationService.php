<?php

declare(strict_types=1);

namespace App\Application\Pipeline\Orchestration;

use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineJob;
use App\Domain\PipelineJob\PipelineJobRepositoryInterface;
use App\Domain\PipelineJob\PipelineJobStatus;

final class PipelineInvalidationService
{
    public function __construct(
        private readonly PipelineJobRepositoryInterface $jobRepository,
        private readonly PipelineDependencyResolver $dependencyResolver,
        private readonly PipelineNotificationService $notificationService,
    ) {
    }

    /**
     * @return list<PipelineJob>
     */
    public function invalidateDependentStages(PipelineJob $sourceJob, array $staleArtifactIds = []): array
    {
        $invalidated = [];
        $invalidatedStageNames = $this->dependencyResolver->invalidatesStages($sourceJob->stage());

        foreach ($this->jobRepository->findBySourceId($sourceJob->sourceId()) as $job) {
            $stageName = $job->stage()->value;
            $isDependent = in_array($stageName, $invalidatedStageNames, true)
                || ($job->dependsOnStage()?->value === $sourceJob->stage()->value);

            if (!$isDependent) {
                continue;
            }

            if (PipelineJobStatus::Completed === $job->status()
                || PipelineJobStatus::WaitingUserConfirmation === $job->status()
                || PipelineJobStatus::Running === $job->status()
                || PipelineJobStatus::Queued === $job->status()) {
                $updated = $job->cancel('Invalidated by restart of '.$sourceJob->stage()->value);

                if ([] !== $staleArtifactIds) {
                    $updated = $updated->markStaleArtifacts($staleArtifactIds);
                }

                $this->jobRepository->save($updated);
                $invalidated[] = $updated;
            }
        }

        if ([] !== $invalidated) {
            $this->notificationService->notifyStagesInvalidated(
                $sourceJob->sourceId(),
                $sourceJob->stage(),
                $invalidatedStageNames,
            );
        }

        return $invalidated;
    }
}
