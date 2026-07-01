<?php

declare(strict_types=1);

namespace App\Application\Collaboration\Queries;

final readonly class ListWorkspaceMembersQuery
{
    public function __construct(public string $workspaceId)
    {
    }
}
