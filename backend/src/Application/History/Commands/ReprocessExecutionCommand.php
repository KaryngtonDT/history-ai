<?php

declare(strict_types=1);

namespace App\Application\History\Commands;

use App\Application\Collaboration\CollaboratorContext;

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
        public string $actorUserId = CollaboratorContext::DEFAULT_USER_ID,
    ) {
    }
}
