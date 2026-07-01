<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'WorkspaceMember',
    required: ['id', 'workspaceId', 'userId', 'displayName', 'role', 'joinedAt'],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'workspaceId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'userId', type: 'string'),
        new OA\Property(property: 'displayName', type: 'string'),
        new OA\Property(property: 'role', ref: '#/components/schemas/WorkspaceRole'),
        new OA\Property(property: 'joinedAt', type: 'string', format: 'date-time'),
    ],
)]
final class WorkspaceMemberSchema
{
}
