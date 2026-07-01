<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'InviteWorkspaceMemberRequest',
    required: ['email', 'role'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'bob@example.com'),
        new OA\Property(property: 'role', ref: '#/components/schemas/WorkspaceRole'),
        new OA\Property(property: 'displayName', type: 'string', nullable: true),
    ],
)]
final class InviteWorkspaceMemberRequestSchema
{
}
