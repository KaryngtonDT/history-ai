<?php

declare(strict_types=1);

namespace App\Application\Collaboration;

use App\Domain\Collaboration\WorkspaceMember;

final class WorkspaceMemberResultMapper
{
    public static function fromMember(WorkspaceMember $member): DTO\WorkspaceMemberResult
    {
        return new DTO\WorkspaceMemberResult(
            $member->id()->value,
            $member->workspaceId()->value,
            $member->userId(),
            $member->displayName(),
            $member->role()->value,
            $member->joinedAt()->format(\DateTimeInterface::ATOM),
        );
    }
}
