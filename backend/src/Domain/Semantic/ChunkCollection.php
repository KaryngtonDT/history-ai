<?php

declare(strict_types=1);

namespace App\Domain\Semantic;

final readonly class ChunkCollection
{
    /** @var list<Chunk> */
    private array $chunks;

    /**
     * @param list<Chunk> $chunks
     */
    public function __construct(array $chunks)
    {
        $this->chunks = array_values($chunks);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<Chunk>
     */
    public function chunks(): array
    {
        return $this->chunks;
    }

    public function count(): int
    {
        return count($this->chunks);
    }

    public function isEmpty(): bool
    {
        return [] === $this->chunks;
    }
}
