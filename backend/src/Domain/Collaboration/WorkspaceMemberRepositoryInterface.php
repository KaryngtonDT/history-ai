<?php

declare(strict_types=1);

namespace App\Domain\Collaboration;

interface WorkspaceMemberRepositoryInterface
{
    public function saveCollection(WorkspaceId $workspaceId, WorkspaceMemberCollection $members): void;

    public function findByWorkspaceId(WorkspaceId $workspaceId): WorkspaceMemberCollection;

    public function findMember(WorkspaceId $workspaceId, string $userId): ?WorkspaceMember;
}
