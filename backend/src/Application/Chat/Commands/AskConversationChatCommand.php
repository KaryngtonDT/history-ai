<?php

declare(strict_types=1);

namespace App\Application\Chat\Commands;

final readonly class AskConversationChatCommand
{
    public function __construct(
        public string $contentId,
        public string $conversationId,
        public string $question,
    ) {
    }
}
