<?php

declare(strict_types=1);

namespace App\Application\AudioUpload\Ports;

use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\Orchestrator\ProcessingStrategy;
use App\Domain\Source\SourceId;

interface AudioProcessingQueueInterface
{
    public function enqueue(
        SourceId $audioId,
        ProcessingMode $processingMode = ProcessingMode::Manual,
        ?ProcessingStrategy $strategy = null,
    ): void;
}
