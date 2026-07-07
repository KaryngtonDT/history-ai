<?php

declare(strict_types=1);

namespace App\Infrastructure\Video;

use App\Application\Video\Ports\VideoProcessingQueueInterface;
use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\Orchestrator\ProcessingStrategy;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\Video\VideoId;
use App\Domain\Workspace\BatchJobId;

final class NoOpVideoProcessingQueue implements VideoProcessingQueueInterface
{
    public function enqueue(
        VideoId $videoId,
        ProcessingMode $processingMode = ProcessingMode::Manual,
        ?ProcessingStrategy $strategy = null,
        ?BatchJobId $batchJobId = null,
        ?PipelineStageType $stage = null,
        ?string $pipelineJobId = null,
    ): void {
    }
}
