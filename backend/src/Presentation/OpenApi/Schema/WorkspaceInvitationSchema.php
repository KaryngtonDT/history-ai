<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'WorkspaceInvitation',
    required: ['id', 'workspaceId', 'email', 'role', 'status', 'token', 'createdAt', 'expiresAt'],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'workspaceId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'email', type: 'string', format: 'email'),
        new OA\Property(property: 'role', ref: '#/components/schemas/WorkspaceRole'),
        new OA\Property(property: 'status', type: 'string', example: 'pending'),
        new OA\Property(property: 'token', type: 'string'),
        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
        new OA\Property(property: 'expiresAt', type: 'string', format: 'date-time'),
    ],
)]
final class WorkspaceInvitationSchema
{
}
