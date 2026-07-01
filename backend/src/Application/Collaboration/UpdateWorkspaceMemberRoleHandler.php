<?php

declare(strict_types=1);

namespace App\Application\Collaboration;

use App\Application\Collaboration\Commands\UpdateWorkspaceMemberRoleCommand;
use App\Application\Collaboration\DTO\WorkspaceMemberResult;
use App\Domain\Collaboration\Exception\InvalidWorkspaceMemberException;
use App\Domain\Collaboration\WorkspaceAction;
use App\Domain\Collaboration\WorkspaceId;
use App\Domain\Collaboration\WorkspaceMemberId;
use App\Domain\Collaboration\WorkspaceMemberRepositoryInterface;

final class UpdateWorkspaceMemberRoleHandler
{
    public function __construct(
        private readonly WorkspaceMemberRepositoryInterface $memberRepository,
    ) {
    }

    public function __invoke(UpdateWorkspaceMemberRoleCommand $command): WorkspaceMemberResult
    {
        $workspaceId = new WorkspaceId($command->workspaceId);
        $actor = $this->memberRepository->findMember($workspaceId, $command->actorUserId);

        if (null === $actor || !$actor->allows(WorkspaceAction::ManageMembers)) {
            throw new InvalidWorkspaceMemberException('Only workspace owners can change member roles.');
        }

        $members = $this->memberRepository->findByWorkspaceId($workspaceId);
        $updated = $members->updateRole(new WorkspaceMemberId($command->memberId), $command->role);
        $this->memberRepository->saveCollection($workspaceId, $updated);

        return WorkspaceMemberResultMapper::fromMember($updated->get(new WorkspaceMemberId($command->memberId)));
    }
}
