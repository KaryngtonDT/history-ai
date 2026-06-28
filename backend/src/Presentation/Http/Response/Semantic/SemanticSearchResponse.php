<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Semantic;

use App\Application\Semantic\DTO\RetrievedChunkResult;
use App\Application\Semantic\DTO\SemanticSearchResult;

final class SemanticSearchResponse
{
    /**
     * @return array{
     *     results: list<array{
     *         artifactId: string,
     *         chunkId: string,
     *         position: int,
     *         text: string,
     *         score: float
     *     }>
     * }
     */
    public static function fromResult(SemanticSearchResult $result): array
    {
        return [
            'results' => array_map(
                static fn (RetrievedChunkResult $retrievedChunk): array => [
                    'artifactId' => $retrievedChunk->artifactId,
                    'chunkId' => $retrievedChunk->chunkId,
                    'position' => $retrievedChunk->position,
                    'text' => $retrievedChunk->text,
                    'score' => $retrievedChunk->score,
                ],
                $result->results,
            ),
        ];
    }
}
