<?php

declare(strict_types=1);

namespace App\Application\Collaboration;

use App\Domain\Collaboration\WorkspaceId;
use App\Domain\Collaboration\WorkspaceMember;
use App\Domain\Collaboration\WorkspaceMemberCollection;
use App\Domain\Collaboration\WorkspaceMemberId;
use App\Domain\Collaboration\WorkspaceMemberRepositoryInterface;
use App\Domain\Collaboration\WorkspaceRole;

final class EnsureWorkspaceOwnerHandler
{
    public function __construct(
        private readonly WorkspaceMemberRepositoryInterface $memberRepository,
    ) {
    }

    public function ensureOwner(WorkspaceId $workspaceId, string $userId, string $displayName): void
    {
        $members = $this->memberRepository->findByWorkspaceId($workspaceId);

        if (!$members->isEmpty()) {
            return;
        }

        $owner = WorkspaceMember::create(
            WorkspaceMemberId::generate(),
            $workspaceId,
            $userId,
            $displayName,
            WorkspaceRole::Owner,
        );

        $this->memberRepository->saveCollection($workspaceId, WorkspaceMemberCollection::empty()->append($owner));
    }
}
