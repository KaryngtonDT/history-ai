<?php

declare(strict_types=1);

namespace App\Domain\Chat;

use App\Domain\Chat\Exception\InvalidChatQuestionException;

final readonly class ChatMessage
{
    private string $content;

    public function __construct(
        private ChatMessageRole $role,
        string $content,
    ) {
        $trimmed = trim($content);

        if ('' === $trimmed) {
            throw new InvalidChatQuestionException('Chat message content cannot be empty.');
        }

        $this->content = $trimmed;
    }

    public function role(): ChatMessageRole
    {
        return $this->role;
    }

    public function content(): string
    {
        return $this->content;
    }

    public function equals(self $other): bool
    {
        return $this->role === $other->role
            && $this->content === $other->content;
    }
}
