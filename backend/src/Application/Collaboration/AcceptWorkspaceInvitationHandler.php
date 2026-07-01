<?php

declare(strict_types=1);

namespace App\Application\Collaboration;

use App\Application\Collaboration\Commands\AcceptWorkspaceInvitationCommand;
use App\Application\Collaboration\DTO\WorkspaceMemberResult;
use App\Domain\Collaboration\Exception\InvalidWorkspaceMemberException;
use App\Domain\Collaboration\WorkspaceInvitationRepositoryInterface;
use App\Domain\Collaboration\WorkspaceMember;
use App\Domain\Collaboration\WorkspaceMemberId;
use App\Domain\Collaboration\WorkspaceMemberRepositoryInterface;
use DateTimeImmutable;

final class AcceptWorkspaceInvitationHandler
{
    public function __construct(
        private readonly WorkspaceInvitationRepositoryInterface $invitationRepository,
        private readonly WorkspaceMemberRepositoryInterface $memberRepository,
    ) {
    }

    public function __invoke(AcceptWorkspaceInvitationCommand $command): WorkspaceMemberResult
    {
        $invitation = $this->invitationRepository->findByToken($command->token);

        if (null === $invitation) {
            throw new InvalidWorkspaceMemberException('Invitation not found.');
        }

        $now = new DateTimeImmutable();
        $accepted = $invitation->accept($now);
        $members = $this->memberRepository->findByWorkspaceId($accepted->workspaceId());

        if ($members->hasUserId($command->userId)) {
            throw new InvalidWorkspaceMemberException('Member already exists in workspace.');
        }

        $member = WorkspaceMember::create(
            WorkspaceMemberId::generate(),
            $accepted->workspaceId(),
            $command->userId,
            $command->displayName,
            $accepted->role(),
            $now,
        );

        $this->memberRepository->saveCollection(
            $accepted->workspaceId(),
            $members->append($member),
        );
        $this->invitationRepository->save($accepted);

        return WorkspaceMemberResultMapper::fromMember($member);
    }
}
