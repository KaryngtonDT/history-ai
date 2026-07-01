<?php

declare(strict_types=1);

namespace App\Application\Workspace\Commands;

use App\Application\Collaboration\CollaboratorContext;

final readonly class DeleteProjectCommand
{
    public function __construct(
        public string $projectId,
        public string $actorUserId = CollaboratorContext::DEFAULT_USER_ID,
    ) {
    }
}
