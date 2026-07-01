<?php

declare(strict_types=1);

namespace App\Application\Workspace\Handlers;

use App\Application\Collaboration\WorkspaceAuthorizationGuard;
use App\Application\Workspace\Commands\DeleteProjectCommand;
use App\Domain\Collaboration\WorkspaceAction;
use App\Domain\Workspace\Exception\InvalidProjectException;
use App\Domain\Workspace\ProjectId;
use App\Domain\Workspace\ProjectRepositoryInterface;

final class DeleteProjectHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly WorkspaceAuthorizationGuard $authorizationGuard,
    ) {
    }

    public function __invoke(DeleteProjectCommand $command): void
    {
        $this->authorizationGuard->assertProjectAction(
            $command->projectId,
            $command->actorUserId,
            WorkspaceAction::DeleteWorkspace,
        );

        $id = new ProjectId($command->projectId);

        if (null === $this->projectRepository->findById($id)) {
            throw new InvalidProjectException('Project not found.');
        }

        $this->projectRepository->delete($id);
    }
}
