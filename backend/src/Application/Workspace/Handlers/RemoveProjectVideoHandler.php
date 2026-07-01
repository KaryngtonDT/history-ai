<?php

declare(strict_types=1);

namespace App\Application\Workspace\Handlers;

use App\Application\Collaboration\WorkspaceAuthorizationGuard;
use App\Application\Workspace\Commands\RemoveProjectVideoCommand;
use App\Application\Workspace\DTO\ProjectResult;
use App\Domain\Collaboration\WorkspaceAction;
use App\Domain\Video\VideoId;
use App\Domain\Workspace\Exception\InvalidProjectException;
use App\Domain\Workspace\ProjectId;
use App\Domain\Workspace\ProjectRepositoryInterface;

final class RemoveProjectVideoHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly WorkspaceAuthorizationGuard $authorizationGuard,
    ) {
    }

    public function __invoke(RemoveProjectVideoCommand $command): ProjectResult
    {
        $this->authorizationGuard->assertProjectAction(
            $command->projectId,
            $command->actorUserId,
            WorkspaceAction::ManageProjects,
        );

        $id = new ProjectId($command->projectId);
        $project = $this->projectRepository->findById($id);

        if (null === $project) {
            throw new InvalidProjectException('Project not found.');
        }

        $updated = $project->removeVideo(new VideoId($command->videoId));
        $this->projectRepository->save($updated);

        return ProjectResult::fromProject($updated);
    }
}
