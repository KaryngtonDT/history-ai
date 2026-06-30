<?php

declare(strict_types=1);

namespace App\Application\Workspace\Commands;

use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\Orchestrator\ProcessingStrategy;

final readonly class ProcessProjectCommand
{
    /**
     * @param list<string> $targetLanguages
     */
    public function __construct(
        public string $projectId,
        public array $targetLanguages,
        public ProcessingMode $processingMode = ProcessingMode::Automatic,
        public ?ProcessingStrategy $strategy = null,
    ) {
    }
}
