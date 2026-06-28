<?php

declare(strict_types=1);

namespace App\Domain\Chat;

use App\Domain\Chat\Exception\InvalidChatStreamException;

final readonly class ChatToken
{
    private string $text;

    public function __construct(string $text)
    {
        if ('' === trim($text)) {
            throw new InvalidChatStreamException('Chat token text cannot be empty.');
        }

        $this->text = $text;
    }

    public function text(): string
    {
        return $this->text;
    }

    public function equals(self $other): bool
    {
        return $this->text === $other->text;
    }
}
