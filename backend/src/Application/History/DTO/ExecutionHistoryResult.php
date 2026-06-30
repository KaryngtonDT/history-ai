<?php

declare(strict_types=1);

namespace App\Application\History\DTO;

final readonly class ExecutionHistoryResult
{
    /**
     * @param list<ExecutionVersionResult> $versions
     */
    public function __construct(
        public string $id,
        public string $videoId,
        public array $versions,
    ) {
    }
}
