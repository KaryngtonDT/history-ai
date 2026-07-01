<?php

declare(strict_types=1);

namespace App\Application\Collaboration;

use App\Application\Collaboration\Commands\RemoveWorkspaceMemberCommand;
use App\Domain\Collaboration\Exception\InvalidWorkspaceMemberException;
use App\Domain\Collaboration\WorkspaceAction;
use App\Domain\Collaboration\WorkspaceId;
use App\Domain\Collaboration\WorkspaceMemberId;
use App\Domain\Collaboration\WorkspaceMemberRepositoryInterface;

final class RemoveWorkspaceMemberHandler
{
    public function __construct(
        private readonly WorkspaceMemberRepositoryInterface $memberRepository,
    ) {
    }

    public function __invoke(RemoveWorkspaceMemberCommand $command): void
    {
        $workspaceId = new WorkspaceId($command->workspaceId);
        $actor = $this->memberRepository->findMember($workspaceId, $command->actorUserId);

        if (null === $actor || !$actor->allows(WorkspaceAction::ManageMembers)) {
            throw new InvalidWorkspaceMemberException('Only workspace owners can remove members.');
        }

        $members = $this->memberRepository->findByWorkspaceId($workspaceId);
        $updated = $members->remove(new WorkspaceMemberId($command->memberId));
        $this->memberRepository->saveCollection($workspaceId, $updated);
    }
}
