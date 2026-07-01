<?php

declare(strict_types=1);

namespace App\Domain\Shadow;

use App\Domain\Shadow\Exception\InvalidShadowSessionException;

final readonly class ShadowChallenge
{
    public function __construct(
        private string $questionText,
        private ?string $suggestedAnswer = null,
    ) {
        if ('' === trim($questionText)) {
            throw new InvalidShadowSessionException('Shadow challenge question cannot be empty.');
        }
    }

    public static function create(string $questionText, ?string $suggestedAnswer = null): self
    {
        return new self(trim($questionText), null !== $suggestedAnswer ? trim($suggestedAnswer) : null);
    }

    public function questionText(): string
    {
        return $this->questionText;
    }

    public function suggestedAnswer(): ?string
    {
        return $this->suggestedAnswer;
    }
}
