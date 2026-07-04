<?php

declare(strict_types=1);

namespace App\Application\YouTube\Commands;

use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\Orchestrator\ProcessingStrategy;

final readonly class ImportYouTubeCommand
{
    public function __construct(
        public string $url,
        public ProcessingMode $processingMode = ProcessingMode::Manual,
        public ?ProcessingStrategy $strategy = null,
        public bool $queueProcessing = true,
    ) {
    }
}
