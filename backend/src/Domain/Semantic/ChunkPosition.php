<?php

declare(strict_types=1);

namespace App\Domain\Semantic;

use App\Domain\Semantic\Exception\InvalidChunkException;

final readonly class ChunkPosition
{
    public function __construct(private int $value)
    {
        if ($value < 0) {
            throw new InvalidChunkException(
                sprintf('Chunk position must be zero or positive, got %d.', $value),
            );
        }
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
