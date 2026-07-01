<?php

declare(strict_types=1);

namespace App\Application\Collaboration\Commands;

final readonly class RemoveWorkspaceMemberCommand
{
    public function __construct(
        public string $workspaceId,
        public string $actorUserId,
        public string $memberId,
    ) {
    }
}
