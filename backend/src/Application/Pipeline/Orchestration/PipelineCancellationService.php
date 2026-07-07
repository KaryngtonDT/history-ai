<?php

declare(strict_types=1);

namespace App\Application\Pipeline\Orchestration;

use App\Domain\PipelineJob\PipelineJob;
use App\Domain\PipelineJob\PipelineJobId;
use App\Domain\PipelineJob\PipelineJobRepositoryInterface;

final class PipelineCancellationService
{
    public function __construct(
        private readonly PipelineJobRepositoryInterface $jobRepository,
        private readonly PipelineNotificationService $notificationService,
    ) {
    }

    public function cancel(PipelineJobId $jobId, string $reason): PipelineJob
    {
        $job = $this->jobRepository->findById($jobId);

        if (null === $job) {
            throw new \RuntimeException('Pipeline job not found.');
        }

        $cancelled = $job->cancel($reason);
        $this->jobRepository->save($cancelled);
        $this->notificationService->notifyStageCancelled($cancelled, $reason);

        return $cancelled;
    }
}
