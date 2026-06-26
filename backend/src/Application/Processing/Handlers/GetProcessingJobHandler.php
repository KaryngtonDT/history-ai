<?php

declare(strict_types=1);

namespace App\Application\Processing\Handlers;

use App\Application\Processing\DTO\GetProcessingJobResult;
use App\Application\Processing\Queries\GetProcessingJobQuery;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Processing\ProcessingJobRepositoryInterface;

final class GetProcessingJobHandler
{
    public function __construct(
        private readonly ProcessingJobRepositoryInterface $processingJobRepository,
    ) {
    }

    public function __invoke(GetProcessingJobQuery $query): ?GetProcessingJobResult
    {
        $job = $this->processingJobRepository->findById(
            new ProcessingJobId($query->processingJobId),
        );

        if (null === $job) {
            return null;
        }

        return GetProcessingJobResult::fromDomain($job);
    }
}
