<?php

declare(strict_types=1);

namespace App\Application\Workspace\Handlers;

use App\Application\Workspace\Commands\CreateProjectCommand;
use App\Application\Workspace\DTO\ProjectResult;
use App\Domain\Workspace\Project;
use App\Domain\Workspace\ProjectId;
use App\Domain\Workspace\ProjectRepositoryInterface;

final class CreateProjectHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
    ) {
    }

    public function __invoke(CreateProjectCommand $command): ProjectResult
    {
        $project = Project::create(ProjectId::generate(), $command->name);
        $this->projectRepository->save($project);

        return ProjectResult::fromProject($project);
    }
}
