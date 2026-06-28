<?php

declare(strict_types=1);

namespace App\Domain\Chat;

use App\Domain\Chat\Exception\InvalidChatQuestionException;

final readonly class ChatQuestion
{
    private const int MIN_LENGTH = 1;

    private const int MAX_LENGTH = 2000;

    private string $value;

    public function __construct(string $raw)
    {
        $trimmed = trim($raw);

        if (strlen($trimmed) < self::MIN_LENGTH) {
            throw new InvalidChatQuestionException('Chat question cannot be empty.');
        }

        if (strlen($trimmed) > self::MAX_LENGTH) {
            throw new InvalidChatQuestionException(
                sprintf('Chat question cannot exceed %d characters.', self::MAX_LENGTH),
            );
        }

        $this->value = $trimmed;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
