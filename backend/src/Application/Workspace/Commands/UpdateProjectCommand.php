<?php

declare(strict_types=1);

namespace App\Application\Workspace\Commands;

final readonly class UpdateProjectCommand
{
    public function __construct(
        public string $projectId,
        public string $name,
    ) {
    }
}
