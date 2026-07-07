<?php

declare(strict_types=1);

namespace App\Infrastructure\Video;

use App\Application\Video\Messages\ProcessVideoMessage;
use App\Application\Video\Ports\VideoProcessingQueueInterface;
use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\Orchestrator\ProcessingStrategy;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\Video\VideoId;
use App\Domain\Workspace\BatchJobId;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessengerVideoProcessingQueue implements VideoProcessingQueueInterface
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function enqueue(
        VideoId $videoId,
        ProcessingMode $processingMode = ProcessingMode::Manual,
        ?ProcessingStrategy $strategy = null,
        ?BatchJobId $batchJobId = null,
        ?PipelineStageType $stage = null,
        ?string $pipelineJobId = null,
    ): void {
        $this->messageBus->dispatch(new ProcessVideoMessage(
            $videoId->value,
            $processingMode,
            $strategy,
            $batchJobId?->value,
            $stage?->value,
            $pipelineJobId,
        ));
    }
}
