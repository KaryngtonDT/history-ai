<?php

declare(strict_types=1);

namespace App\Application\Video\Ports;

use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\Orchestrator\ProcessingStrategy;
use App\Domain\Video\VideoId;

interface VideoProcessingQueueInterface
{
    public function enqueue(
        VideoId $videoId,
        ProcessingMode $processingMode = ProcessingMode::Manual,
        ?ProcessingStrategy $strategy = null,
    ): void;
}
