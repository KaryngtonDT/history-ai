<?php

declare(strict_types=1);

namespace App\Domain\Collaboration;

interface WorkspaceInvitationRepositoryInterface
{
    public function save(WorkspaceInvitation $invitation): void;

    /**
     * @return list<WorkspaceInvitation>
     */
    public function findPendingByWorkspaceId(WorkspaceId $workspaceId): array;

    public function findByToken(string $token): ?WorkspaceInvitation;
}
