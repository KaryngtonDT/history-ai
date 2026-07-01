<?php

declare(strict_types=1);

namespace App\Application\Workspace\Handlers;

use App\Application\Collaboration\WorkspaceAuthorizationGuard;
use App\Application\Workspace\Commands\AddProjectVideoCommand;
use App\Application\Workspace\DTO\ProjectResult;
use App\Domain\Collaboration\WorkspaceAction;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\Workspace\Exception\InvalidProjectException;
use App\Domain\Workspace\ProjectId;
use App\Domain\Workspace\ProjectRepositoryInterface;
use App\Domain\Workspace\ProjectVideo;

final class AddProjectVideoHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly VideoRepositoryInterface $videoRepository,
        private readonly WorkspaceAuthorizationGuard $authorizationGuard,
    ) {
    }

    public function __invoke(AddProjectVideoCommand $command): ProjectResult
    {
        $this->authorizationGuard->assertProjectAction(
            $command->projectId,
            $command->actorUserId,
            WorkspaceAction::Upload,
        );

        $projectId = new ProjectId($command->projectId);
        $project = $this->projectRepository->findById($projectId);

        if (null === $project) {
            throw new InvalidProjectException('Project not found.');
        }

        $videoId = new VideoId($command->videoId);
        $videoJob = $this->videoRepository->findById($videoId);

        if (null === $videoJob) {
            throw new InvalidProjectException('Video not found.');
        }

        $updated = $project->addVideo(ProjectVideo::create($videoId, $videoJob->originalFilename()));
        $this->projectRepository->save($updated);

        return ProjectResult::fromProject($updated);
    }
}
