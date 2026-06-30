<?php

declare(strict_types=1);

namespace App\Application\History\Commands;

final readonly class ReprocessExecutionCommand
{
    /**
     * @param array<string, string> $providerOverrides
     */
    public function __construct(
        public string $videoId,
        public int $versionNumber,
        public array $providerOverrides = [],
        public ?string $batchJobId = null,
    ) {
    }
}
