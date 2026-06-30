<?php

declare(strict_types=1);

namespace App\Application\Workspace\Commands;

final readonly class CreateProjectCommand
{
    public function __construct(public string $name)
    {
    }
}
