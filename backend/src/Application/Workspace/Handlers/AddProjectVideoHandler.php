<?php

declare(strict_types=1);

namespace App\Application\Workspace\Handlers;

use App\Application\Workspace\Commands\AddProjectVideoCommand;
use App\Application\Workspace\DTO\ProjectResult;
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
    ) {
    }

    public function __invoke(AddProjectVideoCommand $command): ProjectResult
    {
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
