<?php

declare(strict_types=1);

namespace App\Application\Semantic\DTO;

use App\Domain\Semantic\RetrievedChunk;
use App\Domain\Semantic\RetrievedChunkCollection;

final readonly class SemanticSearchResult
{
    /**
     * @param list<RetrievedChunkResult> $results
     */
    public function __construct(public array $results)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public static function fromDomain(RetrievedChunkCollection $collection): self
    {
        return new self(
            array_map(
                static fn (RetrievedChunk $retrievedChunk): RetrievedChunkResult => RetrievedChunkResult::fromDomain($retrievedChunk),
                $collection->retrievedChunks(),
            ),
        );
    }
}
