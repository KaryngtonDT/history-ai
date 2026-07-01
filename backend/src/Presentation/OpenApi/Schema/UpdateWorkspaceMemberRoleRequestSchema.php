<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateWorkspaceMemberRoleRequest',
    required: ['role'],
    properties: [
        new OA\Property(property: 'role', ref: '#/components/schemas/WorkspaceRole'),
    ],
)]
final class UpdateWorkspaceMemberRoleRequestSchema
{
}
