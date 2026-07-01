<?php

declare(strict_types=1);

namespace App\Application\AudioUpload\Messages;

use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\Orchestrator\ProcessingStrategy;

final readonly class ProcessAudioMessage
{
    public function __construct(
        public string $audioId,
        public ProcessingMode $processingMode = ProcessingMode::Manual,
        public ?ProcessingStrategy $strategy = null,
    ) {
    }
}
