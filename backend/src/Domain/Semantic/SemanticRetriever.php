<?php

declare(strict_types=1);

namespace App\Domain\Semantic;

use App\Domain\Artifact\ArtifactId;

final class SemanticRetriever
{
    private const int DEFAULT_LIMIT = 5;

    private const string QUERY_ARTIFACT_ID = '00000000-0000-4000-8000-000000000001';

    public function __construct(
        private readonly VectorStoreInterface $vectorStore,
    ) {
    }

    public function retrieve(
        SemanticQuery $query,
        EmbeddingGeneratorInterface $embeddingGenerator,
        int $limit = self::DEFAULT_LIMIT,
    ): RetrievedChunkCollection {
        if ($limit <= 0) {
            return RetrievedChunkCollection::empty();
        }

        $queryVector = $this->embedQuery($query, $embeddingGenerator);
        $searchResults = $this->vectorStore->search($queryVector, $limit);

        if ($searchResults->isEmpty()) {
            return RetrievedChunkCollection::empty();
        }

        /** @var list<RetrievedChunk> $retrievedChunks */
        $retrievedChunks = array_map(
            static fn (VectorSearchResult $result): RetrievedChunk => new RetrievedChunk(
                $result->document()->chunk(),
                $result->score(),
            ),
            $searchResults->results(),
        );

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
}
