<?php

declare(strict_types=1);

namespace App\Application\Collaboration;

use App\Domain\Collaboration\WorkspaceInvitation;

final class WorkspaceInvitationResultMapper
{
    public static function fromInvitation(WorkspaceInvitation $invitation): DTO\WorkspaceInvitationResult
    {
        return new DTO\WorkspaceInvitationResult(
            $invitation->id()->value,
            $invitation->workspaceId()->value,
            $invitation->email(),
            $invitation->role()->value,
            $invitation->status()->value,
            $invitation->token(),
            $invitation->createdAt()->format(\DateTimeInterface::ATOM),
            $invitation->expiresAt()->format(\DateTimeInterface::ATOM),
        );
    }
}
