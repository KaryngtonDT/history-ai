<?php

declare(strict_types=1);

namespace App\Domain\Semantic;

final readonly class VectorSearchResultCollection
{
    /** @var list<VectorSearchResult> */
    private array $results;

    /**
     * @param list<VectorSearchResult> $results
     */
    public function __construct(array $results)
    {
        $this->results = array_values($results);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<VectorSearchResult>
     */
    public function results(): array
    {
        return $this->results;
    }

    public function count(): int
    {
        return count($this->results);
    }

    public function isEmpty(): bool
    {
        return [] === $this->results;
    }
}
