<?php

declare(strict_types=1);

namespace App\Domain\Collaboration;

use App\Domain\Collaboration\Exception\InvalidWorkspaceMemberException;
use DateTimeImmutable;

final readonly class WorkspaceMember
{
    public function __construct(
        private WorkspaceMemberId $id,
        private WorkspaceId $workspaceId,
        private string $userId,
        private string $displayName,
        private WorkspaceRole $role,
        private DateTimeImmutable $joinedAt,
    ) {
        if ('' === trim($userId)) {
            throw new InvalidWorkspaceMemberException('Member user id cannot be empty.');
        }

        if ('' === trim($displayName)) {
            throw new InvalidWorkspaceMemberException('Member display name cannot be empty.');
        }
    }

    public static function create(
        WorkspaceMemberId $id,
        WorkspaceId $workspaceId,
        string $userId,
        string $displayName,
        WorkspaceRole $role,
        ?DateTimeImmutable $joinedAt = null,
    ): self {
        return new self(
            $id,
            $workspaceId,
            trim($userId),
            trim($displayName),
            $role,
            $joinedAt ?? new DateTimeImmutable(),
        );
    }

    public function withRole(WorkspaceRole $role): self
    {
        return new self(
            $this->id,
            $this->workspaceId,
            $this->userId,
            $this->displayName,
            $role,
            $this->joinedAt,
        );
    }

    public function id(): WorkspaceMemberId
    {
        return $this->id;
    }

    public function workspaceId(): WorkspaceId
    {
        return $this->workspaceId;
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function displayName(): string
    {
        return $this->displayName;
    }

    public function role(): WorkspaceRole
    {
        return $this->role;
    }

    public function joinedAt(): DateTimeImmutable
    {
        return $this->joinedAt;
    }

    public function allows(WorkspaceAction $action): bool
    {
        return $this->role->allows($action);
    }
}
