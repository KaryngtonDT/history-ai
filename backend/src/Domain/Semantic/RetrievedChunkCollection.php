<?php

declare(strict_types=1);

namespace App\Domain\Semantic;

final readonly class RetrievedChunkCollection
{
    /** @var list<RetrievedChunk> */
    private array $retrievedChunks;

    /**
     * @param list<RetrievedChunk> $retrievedChunks
     */
    public function __construct(array $retrievedChunks)
    {
        $this->retrievedChunks = array_values($retrievedChunks);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<RetrievedChunk>
     */
    public function retrievedChunks(): array
    {
        return $this->retrievedChunks;
    }

    public function count(): int
    {
        return count($this->retrievedChunks);
    }

    public function isEmpty(): bool
    {
        return [] === $this->retrievedChunks;
    }
}
