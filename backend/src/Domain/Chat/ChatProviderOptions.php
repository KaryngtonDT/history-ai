<?php

declare(strict_types=1);

namespace App\Domain\Chat;

use App\Domain\Chat\Exception\InvalidChatQuestionException;

final readonly class ChatProviderOptions
{
    public const float DEFAULT_TEMPERATURE = 0.2;
    public const int DEFAULT_MAX_TOKENS = 1024;

    public function __construct(
        private float $temperature = self::DEFAULT_TEMPERATURE,
        private int $maxTokens = self::DEFAULT_MAX_TOKENS,
        private ?ChatModel $model = null,
    ) {
        if ($temperature < 0.0 || $temperature > 2.0) {
            throw new InvalidChatQuestionException('Chat temperature must be between 0 and 2.');
        }

        if ($maxTokens < 1) {
            throw new InvalidChatQuestionException('Chat max tokens must be at least 1.');
        }
    }

    public static function defaults(): self
    {
        return new self();
    }

    public function temperature(): float
    {
        return $this->temperature;
    }

    public function maxTokens(): int
    {
        return $this->maxTokens;
    }

    public function model(): ?ChatModel
    {
        return $this->model;
    }
}
