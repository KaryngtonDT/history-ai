<?php

declare(strict_types=1);

namespace App\Infrastructure\Audio;

use App\Application\AudioUpload\Handlers\ProcessAudioHandler;
use App\Application\AudioUpload\Messages\ProcessAudioMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ProcessAudioMessageHandler
{
    public function __construct(
        private readonly ProcessAudioHandler $processAudioHandler,
    ) {
    }

    public function __invoke(ProcessAudioMessage $message): void
    {
        ($this->processAudioHandler)($message);
    }
}
