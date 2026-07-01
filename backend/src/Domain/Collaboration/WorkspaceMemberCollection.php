<?php

declare(strict_types=1);

namespace App\Domain\Collaboration;

use App\Domain\Collaboration\Exception\InvalidWorkspaceMemberException;

final readonly class WorkspaceMemberCollection
{
    /** @var list<WorkspaceMember> */
    private array $members;

    /**
     * @param list<WorkspaceMember> $members
     */
    public function __construct(array $members = [])
    {
        $seen = [];

        foreach ($members as $member) {
            $userId = strtolower($member->userId());

            if (isset($seen[$userId])) {
                throw new InvalidWorkspaceMemberException(sprintf(
                    'Duplicate workspace member "%s".',
                    $member->userId(),
                ));
            }

            $seen[$userId] = true;
        }

        $this->members = array_values($members);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<WorkspaceMember>
     */
    public function all(): array
    {
        return $this->members;
    }

    public function count(): int
    {
        return count($this->members);
    }

    public function isEmpty(): bool
    {
        return [] === $this->members;
    }

    public function append(WorkspaceMember $member): self
    {
        if ($this->hasUserId($member->userId())) {
            throw new InvalidWorkspaceMemberException(sprintf(
                'Member "%s" already exists in workspace.',
                $member->userId(),
            ));
        }

        return new self([...$this->members, $member]);
    }

    public function remove(WorkspaceMemberId $memberId): self
    {
        $member = $this->get($memberId);

        if (WorkspaceRole::Owner === $member->role() && 1 === $this->ownerCount()) {
            throw new InvalidWorkspaceMemberException('Workspace must keep at least one owner.');
        }

        return new self(array_values(array_filter(
            $this->members,
            static fn (WorkspaceMember $entry): bool => !$entry->id()->equals($memberId),
        )));
    }

    public function updateRole(WorkspaceMemberId $memberId, WorkspaceRole $role): self
    {
        $member = $this->get($memberId);

        if (WorkspaceRole::Owner === $member->role() && WorkspaceRole::Owner !== $role && 1 === $this->ownerCount()) {
            throw new InvalidWorkspaceMemberException('Workspace must keep at least one owner.');
        }

        return new self(array_map(
            static fn (WorkspaceMember $entry): WorkspaceMember => $entry->id()->equals($memberId)
                ? $entry->withRole($role)
                : $entry,
            $this->members,
        ));
    }

    public function hasUserId(string $userId): bool
    {
        $normalized = strtolower(trim($userId));

        foreach ($this->members as $member) {
            if (strtolower($member->userId()) === $normalized) {
                return true;
            }
        }

        return false;
    }

    public function findByUserId(string $userId): ?WorkspaceMember
    {
        $normalized = strtolower(trim($userId));

        foreach ($this->members as $member) {
            if (strtolower($member->userId()) === $normalized) {
                return $member;
            }
        }

        return null;
    }

    public function get(WorkspaceMemberId $memberId): WorkspaceMember
    {
        foreach ($this->members as $member) {
            if ($member->id()->equals($memberId)) {
                return $member;
            }
        }

        throw new InvalidWorkspaceMemberException('Workspace member not found.');
    }

    public function ownerCount(): int
    {
        return count(array_filter(
            $this->members,
            static fn (WorkspaceMember $member): bool => WorkspaceRole::Owner === $member->role(),
        ));
    }
}
