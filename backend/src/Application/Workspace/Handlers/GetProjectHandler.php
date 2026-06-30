<?php

declare(strict_types=1);

namespace App\Application\Workspace\Handlers;

use App\Application\Workspace\DTO\ProjectResult;
use App\Application\Workspace\Queries\GetProjectQuery;
use App\Domain\Workspace\BatchJobRepositoryInterface;
use App\Domain\Workspace\Exception\InvalidProjectException;
use App\Domain\Workspace\ProjectId;
use App\Domain\Workspace\ProjectRepositoryInterface;

final class GetProjectHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly BatchJobRepositoryInterface $batchJobRepository,
    ) {
    }

    public function __invoke(GetProjectQuery $query): ProjectResult
    {
        $projectId = new ProjectId($query->projectId);
        $project = $this->projectRepository->findById($projectId);

        if (null === $project) {
            throw new InvalidProjectException('Project not found.');
        }

        $batchJob = $this->batchJobRepository->findLatestByProjectId($projectId);

        return ProjectResult::fromProject($project, $batchJob);
    }
}
