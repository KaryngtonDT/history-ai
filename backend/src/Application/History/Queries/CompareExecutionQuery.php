<?php

declare(strict_types=1);

namespace App\Application\History\Queries;

final readonly class CompareExecutionQuery
{
    public function __construct(
        public string $videoId,
        public int $leftVersion,
        public int $rightVersion,
    ) {
    }
}
