<?php

declare(strict_types=1);

namespace App\Domain\Semantic;

final readonly class EmbeddingCacheKey
{
    private function __construct(public string $value)
    {
        if ('' === $value) {
            throw new \InvalidArgumentException('Embedding cache key cannot be empty.');
        }
    }

    public static function fromChunkText(ChunkText $text): self
    {
        return new self(hash('sha256', $text->value()));
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
