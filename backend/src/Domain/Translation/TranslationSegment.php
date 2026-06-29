<?php

declare(strict_types=1);

namespace App\Domain\Translation;

use App\Domain\Translation\Exception\InvalidTranslationException;

final readonly class TranslationSegment
{
    public function __construct(
        private int $index,
        private string $sourceText,
        private string $translatedText,
    ) {
        if ($index < 0) {
            throw new InvalidTranslationException('Translation segment index cannot be negative.');
        }

        if ('' === trim($translatedText)) {
            throw new InvalidTranslationException('Translation segment translated text cannot be empty.');
        }
    }

    public static function create(
        int $index,
        string $sourceText,
        string $translatedText,
    ): self {
        return new self($index, trim($sourceText), trim($translatedText));
    }

    public function index(): int
    {
        return $this->index;
    }

    public function sourceText(): string
    {
        return $this->sourceText;
    }

    public function translatedText(): string
    {
        return $this->translatedText;
    }
}
