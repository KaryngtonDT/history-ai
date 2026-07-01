<?php

declare(strict_types=1);

namespace App\Application\Collaboration\Commands;

use App\Domain\Collaboration\WorkspaceRole;

final readonly class UpdateWorkspaceMemberRoleCommand
{
    public function __construct(
        public string $workspaceId,
        public string $actorUserId,
        public string $memberId,
        public WorkspaceRole $role,
    ) {
    }
}
