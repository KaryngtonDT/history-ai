<?php

declare(strict_types=1);

namespace App\Application\Chat\Commands;

final readonly class AskContentChatCommand
{
    public function __construct(
        public string $contentId,
        public string $question,
    ) {
    }
}
