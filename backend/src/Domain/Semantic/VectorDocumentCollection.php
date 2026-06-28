<?php

declare(strict_types=1);

namespace App\Domain\Semantic;

final readonly class VectorDocumentCollection
{
    /** @var list<VectorDocument> */
    private array $documents;

    /**
     * @param list<VectorDocument> $documents
     */
    public function __construct(array $documents)
    {
        $this->documents = array_values($documents);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<VectorDocument>
     */
    public function documents(): array
    {
        return $this->documents;
    }

    public function count(): int
    {
        return count($this->documents);
    }

    public function isEmpty(): bool
    {
        return [] === $this->documents;
    }
}
