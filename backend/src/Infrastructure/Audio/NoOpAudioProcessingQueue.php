<?php

declare(strict_types=1);

namespace App\Infrastructure\Audio;

use App\Application\AudioUpload\Ports\AudioProcessingQueueInterface;
use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\Orchestrator\ProcessingStrategy;
use App\Domain\Source\SourceId;

final class NoOpAudioProcessingQueue implements AudioProcessingQueueInterface
{
    public function enqueue(
        SourceId $audioId,
        ProcessingMode $processingMode = ProcessingMode::Manual,
        ?ProcessingStrategy $strategy = null,
    ): void {
    }
}
