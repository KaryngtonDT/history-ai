<?php

declare(strict_types=1);

namespace App\Domain\Semantic;

final readonly class EmbeddedChunkCollection
{
    /** @var list<EmbeddedChunk> */
    private array $embeddedChunks;

    /**
     * @param list<EmbeddedChunk> $embeddedChunks
     */
    public function __construct(array $embeddedChunks)
    {
        $this->embeddedChunks = array_values($embeddedChunks);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<EmbeddedChunk>
     */
    public function embeddedChunks(): array
    {
        return $this->embeddedChunks;
    }

    public function count(): int
    {
        return count($this->embeddedChunks);
    }

    public function isEmpty(): bool
    {
        return [] === $this->embeddedChunks;
    }
}
