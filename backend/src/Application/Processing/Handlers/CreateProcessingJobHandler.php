<?php

declare(strict_types=1);

namespace App\Application\Processing\Handlers;

use App\Application\Processing\Commands\CreateProcessingJobCommand;
use App\Application\Processing\DTO\CreateProcessingJobResult;
use App\Domain\Content\ContentId;
use App\Domain\Processing\ProcessingJob;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Processing\ProcessingJobRepositoryInterface;

final class CreateProcessingJobHandler
{
    public function __construct(
        private readonly ProcessingJobRepositoryInterface $processingJobRepository,
    ) {
    }

    public function __invoke(CreateProcessingJobCommand $command): CreateProcessingJobResult
    {
        $job = ProcessingJob::create(
            ProcessingJobId::generate(),
            new ContentId($command->contentId),
            $command->type,
        );

        $this->processingJobRepository->save($job);

        return new CreateProcessingJobResult(
            $job->id(),
            $job->status(),
            $job->progress()->percentage(),
        );
    }
}
