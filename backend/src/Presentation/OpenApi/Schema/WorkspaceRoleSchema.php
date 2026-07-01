<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'WorkspaceRole',
    type: 'string',
    enum: ['owner', 'editor', 'reviewer', 'viewer'],
    example: 'editor',
)]
final class WorkspaceRoleSchema
{
}
