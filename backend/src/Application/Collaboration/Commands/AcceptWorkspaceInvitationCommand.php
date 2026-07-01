<?php

declare(strict_types=1);

namespace App\Application\Collaboration\Commands;

final readonly class AcceptWorkspaceInvitationCommand
{
    public function __construct(
        public string $token,
        public string $userId,
        public string $displayName,
    ) {
    }
}
