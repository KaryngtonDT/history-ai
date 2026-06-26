<?php

declare(strict_types=1);

namespace App\Application\Processing\Handlers;

use App\Application\Processing\Commands\UpdateProcessingProgressCommand;
use App\Domain\Processing\Exception\ProcessingJobNotFoundException;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Processing\ProcessingJobProgress;
use App\Domain\Processing\ProcessingJobRepositoryInterface;

final class UpdateProcessingProgressHandler
{
    public function __construct(
        private readonly ProcessingJobRepositoryInterface $processingJobRepository,
    ) {
    }

    public function __invoke(UpdateProcessingProgressCommand $command): void
    {
        $job = $this->processingJobRepository->findById(
            new ProcessingJobId($command->processingJobId),
        );

        if (null === $job) {
            throw new ProcessingJobNotFoundException('Processing job not found.');
        }

        $job->updateProgress(ProcessingJobProgress::fromPercentage($command->progress));
        $this->processingJobRepository->save($job);
    }
}
