<?php

declare(strict_types=1);

namespace App\Application\Workspace\Handlers;

use App\Application\Workspace\DTO\ProjectResult;
use App\Domain\Video\VideoId;
use App\Domain\Workspace\Exception\InvalidProjectException;
use App\Domain\Workspace\ProjectId;
use App\Domain\Workspace\ProjectRepositoryInterface;

final class RemoveProjectVideoHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
    ) {
    }

    public function __invoke(string $projectId, string $videoId): ProjectResult
    {
        $id = new ProjectId($projectId);
        $project = $this->projectRepository->findById($id);

        if (null === $project) {
            throw new InvalidProjectException('Project not found.');
        }

        $updated = $project->removeVideo(new VideoId($videoId));
        $this->projectRepository->save($updated);

        return ProjectResult::fromProject($updated);
    }
}
