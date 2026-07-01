<?php

declare(strict_types=1);

namespace App\Application\Collaboration\DTO;

final readonly class WorkspaceMemberResult
{
    public function __construct(
        public string $id,
        public string $workspaceId,
        public string $userId,
        public string $displayName,
        public string $role,
        public string $joinedAt,
    ) {
    }
}
