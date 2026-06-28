<?php

declare(strict_types=1);

namespace App\Domain\Chat;

use App\Domain\Chat\Exception\InvalidChatCitationException;

final readonly class ChatCitation
{
    public function __construct(
        private int $number,
        private ChatSource $source,
    ) {
        if ($number < 1) {
            throw new InvalidChatCitationException(
                sprintf('Chat citation number must be at least 1, got %d.', $number),
            );
        }
    }

    public function number(): int
    {
        return $this->number;
    }

    public function source(): ChatSource
    {
        return $this->source;
    }

    public function equals(self $other): bool
    {
        return $this->number === $other->number
            && $this->source->equals($other->source);
    }
}
