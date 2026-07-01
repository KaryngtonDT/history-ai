<?php

declare(strict_types=1);

namespace App\Domain\Shadow;

use App\Domain\Shadow\Exception\InvalidShadowSessionException;

final readonly class ShadowQuestion
{
    public function __construct(private string $text)
    {
        if ('' === trim($text)) {
            throw new InvalidShadowSessionException('Shadow question text cannot be empty.');
        }
    }

    public static function fromString(string $text): self
    {
        return new self(trim($text));
    }

    public function text(): string
    {
        return $this->text;
    }
}
