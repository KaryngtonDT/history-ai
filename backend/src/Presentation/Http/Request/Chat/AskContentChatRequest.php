<?php

declare(strict_types=1);

namespace App\Presentation\Http\Request\Chat;

use App\Domain\Chat\ChatQuestion;
use App\Domain\Chat\Exception\InvalidChatQuestionException;
use App\Presentation\Http\Request\Chat\Exception\InvalidChatRequestException;

final readonly class AskContentChatRequest
{
    public function __construct(public string $question)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        if (!isset($payload['question'])) {
            throw new InvalidChatRequestException('Question is required.');
        }

        if (!is_string($payload['question'])) {
            throw new InvalidChatRequestException('Question is required.');
        }

        try {
            new ChatQuestion($payload['question']);
        } catch (InvalidChatQuestionException) {
            throw new InvalidChatRequestException('Question is invalid.');
        }

        return new self($payload['question']);
    }
}
