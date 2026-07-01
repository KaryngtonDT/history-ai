<?php

declare(strict_types=1);

namespace App\Application\Collaboration;

use App\Domain\Collaboration\Exception\InvalidWorkspaceMemberException;
use App\Domain\Collaboration\WorkspaceAction;
use App\Domain\Collaboration\WorkspaceId;
use App\Domain\Collaboration\WorkspaceMemberRepositoryInterface;

final class WorkspaceAuthorizationService
{
    public function __construct(
        private readonly WorkspaceMemberRepositoryInterface $memberRepository,
    ) {
    }

    public function assertAllowed(string $workspaceId, string $userId, WorkspaceAction $action): void
    {
        if ($this->isAllowed($workspaceId, $userId, $action)) {
            return;
        }

        throw new InvalidWorkspaceMemberException(sprintf(
            'User "%s" is not allowed to perform "%s" on workspace "%s".',
            $userId,
            $action->value,
            $workspaceId,
        ));
    }

    public function isAllowed(string $workspaceId, string $userId, WorkspaceAction $action): bool
    {
        $members = $this->memberRepository->findByWorkspaceId(new WorkspaceId($workspaceId));

        if ($members->isEmpty()) {
            return true;
        }

        $member = $members->findByUserId($userId);

        return null !== $member && $member->allows($action);
    }
}
