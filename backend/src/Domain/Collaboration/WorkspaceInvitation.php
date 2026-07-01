<?php

declare(strict_types=1);

namespace App\Domain\Collaboration;

use App\Domain\Collaboration\Exception\InvalidWorkspaceMemberException;
use DateTimeImmutable;

final readonly class WorkspaceInvitation
{
    public function __construct(
        private WorkspaceInvitationId $id,
        private WorkspaceId $workspaceId,
        private string $email,
        private WorkspaceRole $role,
        private string $token,
        private WorkspaceInvitationStatus $status,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $expiresAt,
    ) {
        if ('' === trim($email) || !filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
            throw new InvalidWorkspaceMemberException('Invitation email must be valid.');
        }

        if ('' === trim($token)) {
            throw new InvalidWorkspaceMemberException('Invitation token cannot be empty.');
        }

        if ($expiresAt <= $createdAt) {
            throw new InvalidWorkspaceMemberException('Invitation expiry must be after creation time.');
        }
    }

    public static function create(
        WorkspaceInvitationId $id,
        WorkspaceId $workspaceId,
        string $email,
        WorkspaceRole $role,
        string $token,
        DateTimeImmutable $expiresAt,
        ?DateTimeImmutable $createdAt = null,
    ): self {
        $created = $createdAt ?? new DateTimeImmutable();

        return new self(
            $id,
            $workspaceId,
            strtolower(trim($email)),
            $role,
            $token,
            WorkspaceInvitationStatus::Pending,
            $created,
            $expiresAt,
        );
    }

    public static function createWithDuration(
        WorkspaceInvitationId $id,
        WorkspaceId $workspaceId,
        string $email,
        WorkspaceRole $role,
        string $token,
        int $durationDays,
        ?DateTimeImmutable $createdAt = null,
    ): self {
        $created = $createdAt ?? new DateTimeImmutable();

        return self::create(
            $id,
            $workspaceId,
            $email,
            $role,
            $token,
            $created->modify(sprintf('+%d days', $durationDays)),
            $created,
        );
    }

    public static function reconstitute(
        WorkspaceInvitationId $id,
        WorkspaceId $workspaceId,
        string $email,
        WorkspaceRole $role,
        string $token,
        WorkspaceInvitationStatus $status,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $expiresAt,
    ): self {
        return new self(
            $id,
            $workspaceId,
            $email,
            $role,
            $token,
            $status,
            $createdAt,
            $expiresAt,
        );
    }

    public function accept(DateTimeImmutable $now): self
    {
        if (WorkspaceInvitationStatus::Pending !== $this->status) {
            throw new InvalidWorkspaceMemberException('Only pending invitations can be accepted.');
        }

        if ($this->isExpired($now)) {
            throw new InvalidWorkspaceMemberException('Invitation has expired.');
        }

        return new self(
            $this->id,
            $this->workspaceId,
            $this->email,
            $this->role,
            $this->token,
            WorkspaceInvitationStatus::Accepted,
            $this->createdAt,
            $this->expiresAt,
        );
    }

    public function markExpired(): self
    {
        return new self(
            $this->id,
            $this->workspaceId,
            $this->email,
            $this->role,
            $this->token,
            WorkspaceInvitationStatus::Expired,
            $this->createdAt,
            $this->expiresAt,
        );
    }

    public function isExpired(DateTimeImmutable $now): bool
    {
        return $now >= $this->expiresAt;
    }

    public function isPending(DateTimeImmutable $now): bool
    {
        return WorkspaceInvitationStatus::Pending === $this->status && !$this->isExpired($now);
    }

    public function id(): WorkspaceInvitationId
    {
        return $this->id;
    }

    public function workspaceId(): WorkspaceId
    {
        return $this->workspaceId;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function role(): WorkspaceRole
    {
        return $this->role;
    }

    public function token(): string
    {
        return $this->token;
    }

    public function status(): WorkspaceInvitationStatus
    {
        return $this->status;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function expiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }
}
