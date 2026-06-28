<?php

declare(strict_types=1);

namespace App\Domain\Semantic;

use App\Domain\Artifact\ArtifactId;

final class SemanticRetriever
{
    private const int DEFAULT_LIMIT = 5;

    private const string QUERY_ARTIFACT_ID = '00000000-0000-4000-8000-000000000001';

    public function retrieve(
        SemanticQuery $query,
        EmbeddedChunkCollection $embeddedChunks,
        EmbeddingGeneratorInterface $embeddingGenerator,
        int $limit = self::DEFAULT_LIMIT,
    ): RetrievedChunkCollection {
        if ($embeddedChunks->isEmpty() || $limit <= 0) {
            return RetrievedChunkCollection::empty();
        }

        $queryVector = $this->embedQuery($query, $embeddingGenerator);

        /** @var list<array{embeddedChunk: EmbeddedChunk, score: SimilarityScore, index: int}> $scoredEntries */
        $scoredEntries = [];

        foreach ($embeddedChunks->embeddedChunks() as $index => $embeddedChunk) {
            $scoredEntries[] = [
                'embeddedChunk' => $embeddedChunk,
                'score' => $this->similarityScore($queryVector, $embeddedChunk->vector()),
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

        /** @var list<RetrievedChunk> $retrievedChunks */
        $retrievedChunks = [];

        foreach (array_slice($scoredEntries, 0, $limit) as $entry) {
            $retrievedChunks[] = new RetrievedChunk(
                $entry['embeddedChunk']->chunk(),
                $entry['score'],
            );
        }

        return new RetrievedChunkCollection($retrievedChunks);
    }

    private function embedQuery(
        SemanticQuery $query,
        EmbeddingGeneratorInterface $embeddingGenerator,
    ): EmbeddingVector {
        $artifactId = new ArtifactId(self::QUERY_ARTIFACT_ID);
        $position = new ChunkPosition(0);
        $queryChunk = new Chunk(
            ChunkId::derive($artifactId, $position),
            $artifactId,
            ChunkText::fromString($query->value()),
            $position,
        );

        $embeddedQuery = $embeddingGenerator->generate(new ChunkCollection([$queryChunk]));

        return $embeddedQuery->embeddedChunks()[0]->vector();
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
