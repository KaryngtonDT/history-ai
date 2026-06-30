<?php

declare(strict_types=1);

namespace App\Application\Scheduler\Queries;

final readonly class GetExecutionScheduleQuery
{
    public function __construct(public string $videoId)
    {
    }
}
