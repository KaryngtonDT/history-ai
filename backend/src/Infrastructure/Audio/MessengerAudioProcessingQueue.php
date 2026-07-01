<?php

declare(strict_types=1);

namespace App\Infrastructure\Audio;

use App\Application\AudioUpload\Messages\ProcessAudioMessage;
use App\Application\AudioUpload\Ports\AudioProcessingQueueInterface;
use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\Orchestrator\ProcessingStrategy;
use App\Domain\Source\SourceId;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessengerAudioProcessingQueue implements AudioProcessingQueueInterface
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function enqueue(
        SourceId $audioId,
        ProcessingMode $processingMode = ProcessingMode::Manual,
        ?ProcessingStrategy $strategy = null,
    ): void {
        $this->messageBus->dispatch(new ProcessAudioMessage(
            $audioId->value,
            $processingMode,
            $strategy,
        ));
    }
}
