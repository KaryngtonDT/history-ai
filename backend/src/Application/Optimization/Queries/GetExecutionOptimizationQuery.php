<?php

declare(strict_types=1);

namespace App\Application\Optimization\Queries;

final readonly class GetExecutionOptimizationQuery
{
    public function __construct(public string $videoId)
    {
    }
}
