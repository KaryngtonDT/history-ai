<?php

declare(strict_types=1);

namespace App\Application\Collaboration;

use App\Domain\Collaboration\WorkspaceAction;
use App\Domain\Video\VideoId;
use App\Domain\Workspace\ProjectRepositoryInterface;

final class WorkspaceAuthorizationGuard
{
    public function __construct(
        private readonly WorkspaceAuthorizationService $authorization,
        private readonly ProjectRepositoryInterface $projectRepository,
    ) {
    }

    public function assertProjectAction(string $projectId, string $userId, WorkspaceAction $action): void
    {
        $this->authorization->assertAllowed($projectId, $userId, $action);
    }

    public function assertVideoAction(string $videoId, string $userId, WorkspaceAction $action): void
    {
        $projectId = $this->projectRepository->findProjectIdByVideoId(new VideoId($videoId));

        if (null === $projectId) {
            return;
        }

        $this->authorization->assertAllowed($projectId->value, $userId, $action);
    }
}
