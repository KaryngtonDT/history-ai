<?php

declare(strict_types=1);

namespace App\Application\History\Queries;

final readonly class GetExecutionHistoryQuery
{
    public function __construct(public string $videoId)
    {
    }
}
