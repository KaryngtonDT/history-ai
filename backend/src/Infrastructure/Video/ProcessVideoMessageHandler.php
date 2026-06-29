<?php

declare(strict_types=1);

namespace App\Infrastructure\Video;

use App\Application\Video\Handlers\ProcessVideoHandler;
use App\Application\Video\Messages\ProcessVideoMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ProcessVideoMessageHandler
{
    public function __construct(
        private readonly ProcessVideoHandler $processVideoHandler,
    ) {
    }

    public function __invoke(ProcessVideoMessage $message): void
    {
        ($this->processVideoHandler)($message);
    }
}
