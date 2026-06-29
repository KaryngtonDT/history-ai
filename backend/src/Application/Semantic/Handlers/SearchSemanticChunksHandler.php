<?php

declare(strict_types=1);

namespace App\Application\Semantic\Handlers;

use App\Application\Platform\ClockInterface;
use App\Application\Platform\PerformanceMetricCollection;
use App\Application\Platform\PerformanceMetricsRecorderInterface;
use App\Application\Platform\PerformanceTimer;
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
        private readonly PerformanceMetricsRecorderInterface $performanceMetricsRecorder,
        private readonly ClockInterface $clock,
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
        $metrics = PerformanceMetricCollection::empty();
        $totalTimer = new PerformanceTimer($this->clock);
        $totalTimer->start();

        $artifacts = $this->artifactRepository->findByContentId(
            new ContentId($query->contentId),
        );

        if ([] === $artifacts) {
            return $this->complete(SemanticSearchResult::empty(), $metrics, $totalTimer);
        }

        $chunkTimer = new PerformanceTimer($this->clock);
        $chunkTimer->start();

        /** @var list<\App\Domain\Semantic\Chunk> $chunks */
        $chunks = [];

        foreach ($artifacts as $artifact) {
            foreach ($this->chunker->chunk($artifact)->chunks() as $chunk) {
                $chunks[] = $chunk;
            }
        }

        $metrics = $metrics->with($chunkTimer->stop('chunking_ms'));

        if ([] === $chunks) {
            return $this->complete(SemanticSearchResult::empty(), $metrics, $totalTimer);
        }

        $embeddingTimer = new PerformanceTimer($this->clock);
        $embeddingTimer->start();
        $embeddedChunks = $this->embeddingGenerator->generate(new ChunkCollection($chunks));
        $metrics = $metrics->with($embeddingTimer->stop('embedding_ms'));

        if ($embeddedChunks->isEmpty()) {
            return $this->complete(SemanticSearchResult::empty(), $metrics, $totalTimer);
        }

        $indexTimer = new PerformanceTimer($this->clock);
        $indexTimer->start();
        $this->vectorStore->index($this->toVectorDocuments($embeddedChunks));
        $metrics = $metrics->with($indexTimer->stop('vector_index_ms'));

        $retrievalTimer = new PerformanceTimer($this->clock);
        $retrievalTimer->start();
        $retrievedChunks = $this->semanticRetriever->retrieve(
            new SemanticQuery($query->query),
            $this->embeddingGenerator,
        );
        $metrics = $metrics->with($retrievalTimer->stop('retrieval_ms'));

        $this->platformLogger->info(self::COMPONENT, 'retrieval completed', [
            'resultCount' => $retrievedChunks->count(),
        ]);

        return $this->complete(SemanticSearchResult::fromDomain($retrievedChunks), $metrics, $totalTimer);
    }

    private function complete(
        SemanticSearchResult $result,
        PerformanceMetricCollection $metrics,
        PerformanceTimer $totalTimer,
    ): SemanticSearchResult {
        $this->performanceMetricsRecorder->record(
            $metrics->with($totalTimer->stop('total_ms')),
        );

        return $result;
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
