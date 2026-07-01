<?php

declare(strict_types=1);

namespace App\Application\Workspace\Commands;

use App\Application\Collaboration\CollaboratorContext;
use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\Orchestrator\ProcessingStrategy;

final readonly class RunBatchProcessingCommand
{
    /**
     * @param list<string> $targetLanguages
     */
    public function __construct(
        public string $projectId,
        public array $targetLanguages,
        public ProcessingMode $processingMode = ProcessingMode::Automatic,
        public ?ProcessingStrategy $strategy = null,
        public string $actorUserId = CollaboratorContext::DEFAULT_USER_ID,
    ) {
    }
}
