<?php

declare(strict_types=1);

namespace App\Application\Semantic\Handlers;

use App\Application\Platform\PlatformLoggerInterface;
use App\Application\Semantic\DTO\SemanticSearchResult;
use App\Application\Semantic\Queries\SearchSemanticChunksQuery;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Content\ContentId;
use App\Domain\Semantic\ChunkCollection;
use App\Domain\Semantic\Chunker;
use App\Domain\Semantic\EmbeddedChunk;
use App\Domain\Semantic\EmbeddedChunkCollection;
use App\Domain\Semantic\EmbeddingGeneratorInterface;
use App\Domain\Semantic\SemanticQuery;
use App\Domain\Semantic\SemanticRetriever;
use App\Domain\Semantic\VectorDocument;
use App\Domain\Semantic\VectorDocumentCollection;
use App\Domain\Semantic\VectorStoreInterface;

final class SearchSemanticChunksHandler
{
    private const string COMPONENT = 'SearchSemanticChunksHandler';

    public function __construct(
        private readonly ArtifactRepositoryInterface $artifactRepository,
        private readonly Chunker $chunker,
        private readonly EmbeddingGeneratorInterface $embeddingGenerator,
        private readonly VectorStoreInterface $vectorStore,
        private readonly SemanticRetriever $semanticRetriever,
        private readonly PlatformLoggerInterface $platformLogger,
    ) {
    }

    public function __invoke(SearchSemanticChunksQuery $query): SemanticSearchResult
    {
        $this->platformLogger->info(self::COMPONENT, 'request started', [
            'contentId' => $query->contentId,
        ]);

        try {
            return $this->handle($query);
        } finally {
            $this->platformLogger->info(self::COMPONENT, 'request completed');
        }
    }

    private function handle(SearchSemanticChunksQuery $query): SemanticSearchResult
    {
        $artifacts = $this->artifactRepository->findByContentId(
            new ContentId($query->contentId),
        );

        if ([] === $artifacts) {
            return SemanticSearchResult::empty();
        }

        /** @var list<\App\Domain\Semantic\Chunk> $chunks */
        $chunks = [];

        foreach ($artifacts as $artifact) {
            foreach ($this->chunker->chunk($artifact)->chunks() as $chunk) {
                $chunks[] = $chunk;
            }
        }

        if ([] === $chunks) {
            return SemanticSearchResult::empty();
        }

        $embeddedChunks = $this->embeddingGenerator->generate(new ChunkCollection($chunks));

        if ($embeddedChunks->isEmpty()) {
            return SemanticSearchResult::empty();
        }

        $this->vectorStore->index($this->toVectorDocuments($embeddedChunks));

        $retrievedChunks = $this->semanticRetriever->retrieve(
            new SemanticQuery($query->query),
            $this->embeddingGenerator,
        );

        $this->platformLogger->info(self::COMPONENT, 'retrieval completed', [
            'resultCount' => $retrievedChunks->count(),
        ]);

        return SemanticSearchResult::fromDomain($retrievedChunks);
    }

    private function toVectorDocuments(EmbeddedChunkCollection $embeddedChunks): VectorDocumentCollection
    {
        /** @var list<VectorDocument> $documents */
        $documents = array_map(
            static fn (EmbeddedChunk $embeddedChunk): VectorDocument => new VectorDocument(
                $embeddedChunk->chunk(),
                $embeddedChunk->vector(),
            ),
            $embeddedChunks->embeddedChunks(),
        );

        return new VectorDocumentCollection($documents);
    }
}
