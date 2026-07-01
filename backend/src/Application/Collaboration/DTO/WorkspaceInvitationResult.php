<?php

declare(strict_types=1);

namespace App\Application\Collaboration\DTO;

final readonly class WorkspaceInvitationResult
{
    public function __construct(
        public string $id,
        public string $workspaceId,
        public string $email,
        public string $role,
        public string $status,
        public string $token,
        public string $createdAt,
        public string $expiresAt,
    ) {
    }
}
