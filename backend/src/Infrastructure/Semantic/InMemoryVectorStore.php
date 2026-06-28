<?php

declare(strict_types=1);

namespace App\Infrastructure\Semantic;

use App\Domain\Semantic\EmbeddingVector;
use App\Domain\Semantic\SimilarityScore;
use App\Domain\Semantic\VectorDocument;
use App\Domain\Semantic\VectorDocumentCollection;
use App\Domain\Semantic\VectorSearchResult;
use App\Domain\Semantic\VectorSearchResultCollection;
use App\Domain\Semantic\VectorStoreInterface;

final class InMemoryVectorStore implements VectorStoreInterface
{
    /** @var list<VectorDocument> */
    private array $documents = [];

    public function index(VectorDocumentCollection $documents): void
    {
        $this->documents = $documents->documents();
    }

    public function search(EmbeddingVector $query, int $limit = 5): VectorSearchResultCollection
    {
        if ([] === $this->documents || $limit <= 0) {
            return VectorSearchResultCollection::empty();
        }

        /** @var list<array{document: VectorDocument, score: SimilarityScore, index: int}> $scoredEntries */
        $scoredEntries = [];

        foreach ($this->documents as $index => $document) {
            $scoredEntries[] = [
                'document' => $document,
                'score' => $this->similarityScore($query, $document->vector()),
                'index' => $index,
            ];
        }

        usort(
            $scoredEntries,
            static function (array $left, array $right): int {
                $scoreComparison = $right['score']->value() <=> $left['score']->value();

                if (0 !== $scoreComparison) {
                    return $scoreComparison;
                }

                return $left['index'] <=> $right['index'];
            },
        );

        /** @var list<VectorSearchResult> $results */
        $results = [];

        foreach (array_slice($scoredEntries, 0, $limit) as $entry) {
            $results[] = new VectorSearchResult(
                $entry['document'],
                $entry['score'],
            );
        }

        return new VectorSearchResultCollection($results);
    }

    private function similarityScore(
        EmbeddingVector $queryVector,
        EmbeddingVector $candidateVector,
    ): SimilarityScore {
        $cosine = $this->cosineSimilarity($queryVector, $candidateVector);
        $normalized = ($cosine + 1.0) / 2.0;

        return new SimilarityScore(min(1.0, max(0.0, $normalized)));
    }

    private function cosineSimilarity(EmbeddingVector $left, EmbeddingVector $right): float
    {
        $leftValues = $left->values();
        $rightValues = $right->values();

        $dotProduct = 0.0;
        $leftMagnitude = 0.0;
        $rightMagnitude = 0.0;

        foreach ($leftValues as $index => $leftValue) {
            $rightValue = $rightValues[$index];
            $dotProduct += $leftValue * $rightValue;
            $leftMagnitude += $leftValue * $leftValue;
            $rightMagnitude += $rightValue * $rightValue;
        }

        if (0.0 === $leftMagnitude || 0.0 === $rightMagnitude) {
            return 0.0;
        }

        return $dotProduct / (sqrt($leftMagnitude) * sqrt($rightMagnitude));
    }
}
