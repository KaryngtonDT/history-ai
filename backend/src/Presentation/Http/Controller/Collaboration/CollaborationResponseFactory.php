<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Collaboration;

use App\Application\Collaboration\DTO\WorkspaceInvitationResult;
use App\Application\Collaboration\DTO\WorkspaceMemberResult;

final class CollaborationResponseFactory
{
    /**
     * @return array<string, mixed>
     */
    public static function memberFromResult(WorkspaceMemberResult $result): array
    {
        return [
            'id' => $result->id,
            'workspaceId' => $result->workspaceId,
            'userId' => $result->userId,
            'displayName' => $result->displayName,
            'role' => $result->role,
            'joinedAt' => $result->joinedAt,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function invitationFromResult(WorkspaceInvitationResult $result): array
    {
        return [
            'id' => $result->id,
            'workspaceId' => $result->workspaceId,
            'email' => $result->email,
            'role' => $result->role,
            'status' => $result->status,
            'token' => $result->token,
            'createdAt' => $result->createdAt,
            'expiresAt' => $result->expiresAt,
        ];
    }
}
