<?php

declare(strict_types=1);

namespace App\Domain\Source;

use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\Orchestrator\ProcessingStrategy;

final readonly class SourceProcessingRequest
{
    public function __construct(
        public SourceId $sourceId,
        public SourceType $type,
        public ProcessingMode $processingMode = ProcessingMode::Manual,
        public ?ProcessingStrategy $strategy = null,
    ) {
    }
}
