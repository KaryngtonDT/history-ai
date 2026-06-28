<?php

declare(strict_types=1);

namespace App\Application\Chat\Commands;

final readonly class AskContentChatStreamCommand
{
    public function __construct(
        public string $contentId,
        public string $question,
    ) {
    }
}
