<?php

declare(strict_types=1);

namespace App\Application\Video\Messages;

use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\Orchestrator\ProcessingStrategy;

final readonly class ProcessVideoMessage
{
    public function __construct(
        public string $videoId,
        public ProcessingMode $processingMode = ProcessingMode::Manual,
        public ?ProcessingStrategy $strategy = null,
        public ?string $batchJobId = null,
        public ?string $stage = null,
        public ?string $pipelineJobId = null,
    ) {
    }
}
