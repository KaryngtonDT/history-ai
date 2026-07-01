<?php

declare(strict_types=1);

namespace App\Application\Workspace\Commands;

use App\Application\Collaboration\CollaboratorContext;

final readonly class CreateProjectCommand
{
    public function __construct(
        public string $name,
        public string $ownerUserId = CollaboratorContext::DEFAULT_USER_ID,
        public string $ownerDisplayName = CollaboratorContext::DEFAULT_DISPLAY_NAME,
    ) {
    }
}
