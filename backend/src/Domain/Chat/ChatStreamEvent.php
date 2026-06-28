<?php

declare(strict_types=1);

namespace App\Domain\Chat;

use App\Domain\Chat\Exception\InvalidChatStreamException;

final readonly class ChatStreamEvent
{
    public function __construct(
        private int $index,
        private ChatToken $token,
    ) {
        if ($index < 0) {
            throw new InvalidChatStreamException(
                sprintf('Chat stream event index must be non-negative, got %d.', $index),
            );
        }
    }

    public function index(): int
    {
        return $this->index;
    }

    public function token(): ChatToken
    {
        return $this->token;
    }

    public function equals(self $other): bool
    {
        return $this->index === $other->index
            && $this->token->equals($other->token);
    }
}
