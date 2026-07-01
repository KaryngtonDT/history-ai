<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Collaboration;

use App\Domain\Collaboration\WorkspaceInvitation;
use App\Domain\Collaboration\WorkspaceInvitationId;
use App\Domain\Collaboration\WorkspaceInvitationStatus;
use App\Domain\Collaboration\WorkspaceId;
use App\Domain\Collaboration\WorkspaceMember;
use App\Domain\Collaboration\WorkspaceMemberId;
use App\Domain\Collaboration\WorkspaceRole;
use DateTimeImmutable;

final class CollaborationJsonMapper
{
    /**
     * @return array<string, mixed>
     */
    public function memberToArray(WorkspaceMember $member): array
    {
        return [
            'id' => $member->id()->value,
            'workspaceId' => $member->workspaceId()->value,
            'userId' => $member->userId(),
            'displayName' => $member->displayName(),
            'role' => $member->role()->value,
            'joinedAt' => $member->joinedAt()->format(DateTimeImmutable::ATOM),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function memberFromArray(array $payload): WorkspaceMember
    {
        return WorkspaceMember::create(
            new WorkspaceMemberId((string) $payload['id']),
            new WorkspaceId((string) $payload['workspaceId']),
            (string) $payload['userId'],
            (string) $payload['displayName'],
            WorkspaceRole::from((string) $payload['role']),
            new DateTimeImmutable((string) $payload['joinedAt']),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function invitationToArray(WorkspaceInvitation $invitation): array
    {
        return [
            'id' => $invitation->id()->value,
            'workspaceId' => $invitation->workspaceId()->value,
            'email' => $invitation->email(),
            'role' => $invitation->role()->value,
            'token' => $invitation->token(),
            'status' => $invitation->status()->value,
            'createdAt' => $invitation->createdAt()->format(DateTimeImmutable::ATOM),
            'expiresAt' => $invitation->expiresAt()->format(DateTimeImmutable::ATOM),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function invitationFromArray(array $payload): WorkspaceInvitation
    {
        return WorkspaceInvitation::reconstitute(
            new WorkspaceInvitationId((string) $payload['id']),
            new WorkspaceId((string) $payload['workspaceId']),
            (string) $payload['email'],
            WorkspaceRole::from((string) $payload['role']),
            (string) $payload['token'],
            WorkspaceInvitationStatus::from((string) $payload['status']),
            new DateTimeImmutable((string) $payload['createdAt']),
            new DateTimeImmutable((string) $payload['expiresAt']),
        );
    }
}
