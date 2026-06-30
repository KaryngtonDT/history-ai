<?php

declare(strict_types=1);

namespace App\Application\Workspace\Queries;

final readonly class GetProjectQuery
{
    public function __construct(public string $projectId)
    {
    }
}
