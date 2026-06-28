<?php

declare(strict_types=1);

namespace App\Domain\Semantic;

use App\Domain\Semantic\Exception\InvalidChunkException;

final readonly class ChunkText
{
    private function __construct(private string $value)
    {
    }

    public static function fromString(string $raw): self
    {
        $value = trim($raw);

        if ('' === $value) {
            throw new InvalidChunkException('Chunk text cannot be empty.');
        }

        return new self($value);
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
