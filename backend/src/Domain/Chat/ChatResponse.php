<?php

declare(strict_types=1);

namespace App\Domain\Chat;

use App\Domain\Chat\Exception\InvalidChatQuestionException;

final readonly class ChatResponse
{
    private string $answer;
    private ChatCitationCollection $citations;

    public function __construct(
        string $answer,
        private ChatSourceCollection $sources,
        ?ChatCitationCollection $citations = null,
    ) {
        $trimmed = trim($answer);

        if ('' === $trimmed) {
            throw new InvalidChatQuestionException('Chat answer cannot be empty.');
        }

        $this->answer = $trimmed;
        $this->citations = $citations ?? ChatCitationCollection::empty();
    }

    public function answer(): string
    {
        return $this->answer;
    }

    public function sources(): ChatSourceCollection
    {
        return $this->sources;
    }

    public function citations(): ChatCitationCollection
    {
        return $this->citations;
    }
}
