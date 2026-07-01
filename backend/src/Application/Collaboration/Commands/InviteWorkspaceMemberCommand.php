<?php

declare(strict_types=1);

namespace App\Application\Collaboration\Commands;

use App\Domain\Collaboration\WorkspaceRole;

final readonly class InviteWorkspaceMemberCommand
{
    public function __construct(
        public string $workspaceId,
        public string $inviterUserId,
        public string $email,
        public WorkspaceRole $role,
        public string $displayName = '',
    ) {
    }
}
