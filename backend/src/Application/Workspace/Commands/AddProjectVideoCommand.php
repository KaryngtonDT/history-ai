<?php

declare(strict_types=1);

namespace App\Application\Workspace\Commands;

use App\Application\Collaboration\CollaboratorContext;

final readonly class AddProjectVideoCommand
{
    public function __construct(
        public string $projectId,
        public string $videoId,
        public string $actorUserId = CollaboratorContext::DEFAULT_USER_ID,
    ) {
    }
}
