<?php

declare(strict_types=1);

namespace App\Application\Collaboration;

use App\Application\Collaboration\Commands\InviteWorkspaceMemberCommand;
use App\Application\Collaboration\DTO\WorkspaceInvitationResult;
use App\Domain\Collaboration\Exception\InvalidWorkspaceMemberException;
use App\Domain\Collaboration\WorkspaceAction;
use App\Domain\Collaboration\WorkspaceId;
use App\Domain\Collaboration\WorkspaceInvitation;
use App\Domain\Collaboration\WorkspaceInvitationId;
use App\Domain\Collaboration\WorkspaceInvitationRepositoryInterface;
use App\Domain\Collaboration\WorkspaceMemberRepositoryInterface;
use App\Domain\Collaboration\WorkspaceRole;

final class InviteWorkspaceMemberHandler
{
    private const int INVITATION_DURATION_DAYS = 7;

    public function __construct(
        private readonly WorkspaceMemberRepositoryInterface $memberRepository,
        private readonly WorkspaceInvitationRepositoryInterface $invitationRepository,
    ) {
    }

    public function __invoke(InviteWorkspaceMemberCommand $command): WorkspaceInvitationResult
    {
        $workspaceId = new WorkspaceId($command->workspaceId);
        $inviter = $this->memberRepository->findMember($workspaceId, $command->inviterUserId);

        if (null === $inviter || !$inviter->allows(WorkspaceAction::ManageMembers)) {
            throw new InvalidWorkspaceMemberException('Only workspace owners can invite members.');
        }

        if (WorkspaceRole::Owner === $command->role && WorkspaceRole::Owner !== $inviter->role()) {
            throw new InvalidWorkspaceMemberException('Only owners can invite another owner.');
        }

        $members = $this->memberRepository->findByWorkspaceId($workspaceId);
        $emailUserId = strtolower($command->email);

        if ($members->hasUserId($emailUserId)) {
            throw new InvalidWorkspaceMemberException('Member already exists in workspace.');
        }

        $token = hash('sha256', $command->email.$command->workspaceId.$command->role->value);
        $invitation = WorkspaceInvitation::createWithDuration(
            WorkspaceInvitationId::generate(),
            $workspaceId,
            $command->email,
            $command->role,
            $token,
            self::INVITATION_DURATION_DAYS,
        );

        $this->invitationRepository->save($invitation);

        return WorkspaceInvitationResultMapper::fromInvitation($invitation);
    }
}
