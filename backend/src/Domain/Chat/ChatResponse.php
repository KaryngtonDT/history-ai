<?php

declare(strict_types=1);

namespace App\Domain\Chat;

use App\Domain\Chat\Exception\InvalidChatQuestionException;

final readonly class ChatResponse
{
    private string $answer;

    public function __construct(
        string $answer,
        private ChatSourceCollection $sources,
    ) {
        $trimmed = trim($answer);

        if ('' === $trimmed) {
            throw new InvalidChatQuestionException('Chat answer cannot be empty.');
        }

        $this->answer = $trimmed;
    }

    public function answer(): string
    {
        return $this->answer;
    }

    public function sources(): ChatSourceCollection
    {
        return $this->sources;
    }
}
