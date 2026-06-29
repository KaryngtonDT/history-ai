<?php

declare(strict_types=1);

namespace App\Infrastructure\Video;

use App\Application\Video\Messages\ProcessVideoMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ProcessVideoMessageHandler
{
    public function __invoke(ProcessVideoMessage $message): void
    {
    }
}
