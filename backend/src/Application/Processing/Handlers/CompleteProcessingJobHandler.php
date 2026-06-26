<?php

declare(strict_types=1);

namespace App\Application\Processing\Handlers;

use App\Application\Processing\Commands\CompleteProcessingJobCommand;
use App\Domain\Processing\Exception\ProcessingJobNotFoundException;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Processing\ProcessingJobRepositoryInterface;

final class CompleteProcessingJobHandler
{
    public function __construct(
        private readonly ProcessingJobRepositoryInterface $processingJobRepository,
    ) {
    }

    public function __invoke(CompleteProcessingJobCommand $command): void
    {
        $job = $this->processingJobRepository->findById(
            new ProcessingJobId($command->processingJobId),
        );

        if (null === $job) {
            throw new ProcessingJobNotFoundException('Processing job not found.');
        }

        $job->complete();
        $this->processingJobRepository->save($job);
    }
}
