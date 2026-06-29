<?php

declare(strict_types=1);

namespace App\Application\Chat\DTO;

use App\Domain\Chat\Conversation;

final readonly class ConversationChatResult
{
    public function __construct(
        public ConversationResult $conversation,
        public ChatAnswerResult $answer,
    ) {
    }

    public static function fromDomain(Conversation $conversation, ChatAnswerResult $answer): self
    {
        return new self(
            conversation: ConversationResult::fromDomain($conversation),
            answer: $answer,
        );
    }
}
