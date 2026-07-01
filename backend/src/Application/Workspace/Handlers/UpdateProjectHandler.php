<?php

declare(strict_types=1);

namespace App\Application\Workspace\Handlers;

use App\Application\Collaboration\WorkspaceAuthorizationGuard;
use App\Application\Workspace\Commands\UpdateProjectCommand;
use App\Application\Workspace\DTO\ProjectResult;
use App\Domain\Collaboration\WorkspaceAction;
use App\Domain\Workspace\Exception\InvalidProjectException;
use App\Domain\Workspace\ProjectId;
use App\Domain\Workspace\ProjectRepositoryInterface;

final class UpdateProjectHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly WorkspaceAuthorizationGuard $authorizationGuard,
    ) {
    }

    public function __invoke(UpdateProjectCommand $command): ProjectResult
    {
        $this->authorizationGuard->assertProjectAction(
            $command->projectId,
            $command->actorUserId,
            WorkspaceAction::ManageProjects,
        );

        $projectId = new ProjectId($command->projectId);
        $project = $this->projectRepository->findById($projectId);

        if (null === $project) {
            throw new InvalidProjectException('Project not found.');
        }

        $updated = $project->rename($command->name);
        $this->projectRepository->save($updated);

        return ProjectResult::fromProject($updated);
    }
}
