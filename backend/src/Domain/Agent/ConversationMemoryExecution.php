<?php

declare(strict_types=1);

namespace App\Domain\Agent;

final readonly class ConversationMemoryExecution
{
    public function __construct(
        private string $conversationId,
        private string $question,
    ) {
    }

    public function conversationId(): string
    {
        return $this->conversationId;
    }

    public function question(): string
    {
        return $this->question;
    }

    public function equals(self $other): bool
    {
        return $this->conversationId === $other->conversationId
            && $this->question === $other->question;
    }
}
