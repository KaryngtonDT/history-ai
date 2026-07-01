<?php

declare(strict_types=1);

namespace App\Domain\YouTube;

use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\Orchestrator\ProcessingStrategy;

final readonly class YouTubeImportRequest
{
    public function __construct(
        public string $url,
        public ProcessingMode $processingMode = ProcessingMode::Manual,
        public ?ProcessingStrategy $strategy = null,
    ) {
    }
}
