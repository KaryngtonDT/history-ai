<?php

declare(strict_types=1);

namespace App\Application\Chat\Handlers;

use App\Application\Chat\Commands\AskContentChatCommand;
use App\Application\Chat\DTO\ChatAnswerResult;
use App\Application\Platform\ClockInterface;
use App\Application\Platform\PerformanceMetricCollection;
use App\Application\Platform\PerformanceMetricsRecorderInterface;
use App\Application\Platform\PerformanceTimer;
use App\Application\Platform\PlatformLoggerInterface;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Chat\ChatContext;
use App\Domain\Chat\ChatOrchestrator;
use App\Domain\Chat\ChatProviderInterface;
use App\Domain\Chat\ChatProviderOptions;
use App\Domain\Chat\ChatQuestion;
use App\Domain\Chat\ChatRequest;
use App\Domain\Content\ContentId;
use App\Domain\Semantic\ChunkCollection;
use App\Domain\Semantic\Chunker;
use App\Domain\Semantic\EmbeddedChunk;
use App\Domain\Semantic\EmbeddedChunkCollection;
use App\Domain\Semantic\EmbeddingGeneratorInterface;
use App\Domain\Semantic\RetrievedChunkCollection;
use App\Domain\Semantic\SemanticQuery;
use App\Domain\Semantic\SemanticRetriever;
use App\Domain\Semantic\VectorDocument;
use App\Domain\Semantic\VectorDocumentCollection;
use App\Domain\Semantic\VectorStoreInterface;

final class AskContentChatHandler
{
    private const int SEMANTIC_QUERY_MAX_LENGTH = 500;
    private const string COMPONENT = 'AskContentChatHandler';

    public function __construct(
        private readonly ArtifactRepositoryInterface $artifactRepository,
        private readonly Chunker $chunker,
        private readonly EmbeddingGeneratorInterface $embeddingGenerator,
        private readonly VectorStoreInterface $vectorStore,
        private readonly SemanticRetriever $semanticRetriever,
        private readonly ChatOrchestrator $chatOrchestrator,
        private readonly ChatProviderInterface $chatProvider,
        private readonly PlatformLoggerInterface $platformLogger,
        private readonly PerformanceMetricsRecorderInterface $performanceMetricsRecorder,
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(AskContentChatCommand $command): ChatAnswerResult
    {
        $this->platformLogger->info(self::COMPONENT, 'request started', [
            'contentId' => $command->contentId,
        ]);

        try {
            return $this->handle($command);
        } finally {
            $this->platformLogger->info(self::COMPONENT, 'request completed');
        }
    }

    private function handle(AskContentChatCommand $command): ChatAnswerResult
    {
        $metrics = PerformanceMetricCollection::empty();
        $totalTimer = new PerformanceTimer($this->clock);
        $totalTimer->start();

        $question = new ChatQuestion($command->question);
        $artifacts = $this->artifactRepository->findByContentId(
            new ContentId($command->contentId),
        );

        if ([] === $artifacts) {
            $providerTimer = new PerformanceTimer($this->clock);
            $providerTimer->start();

            $context = new ChatContext($question, RetrievedChunkCollection::empty());
            $prompt = $this->chatOrchestrator->buildPrompt($context);
            $response = $this->chatProvider->answer(ChatRequest::create(
                $prompt,
                $context->sources(),
                ChatProviderOptions::defaults(),
            ));

            $metrics = $metrics->with($providerTimer->stop('provider_ms'));

            $this->platformLogger->info(self::COMPONENT, 'retrieval completed', [
                'chunkCount' => 0,
            ]);
            $this->platformLogger->info(self::COMPONENT, 'provider completed');

            return $this->complete(ChatAnswerResult::fromDomain($response), $metrics, $totalTimer);
        }

        [$retrievedChunks, $ragMetrics] = $this->measureRetrieval($artifacts, $question);
        $metrics = $metrics->merge($ragMetrics);

        $this->platformLogger->info(self::COMPONENT, 'retrieval completed', [
            'chunkCount' => $retrievedChunks->count(),
        ]);

        $providerTimer = new PerformanceTimer($this->clock);
        $providerTimer->start();

        $context = new ChatContext($question, $retrievedChunks);
        $prompt = $this->chatOrchestrator->buildPrompt($context);
        $response = $this->chatProvider->answer(ChatRequest::create(
            $prompt,
            $context->sources(),
            ChatProviderOptions::defaults(),
        ));

        $metrics = $metrics->with($providerTimer->stop('provider_ms'));

        $this->platformLogger->info(self::COMPONENT, 'provider completed');

        return $this->complete(ChatAnswerResult::fromDomain($response), $metrics, $totalTimer);
    }

    /**
     * @param list<\App\Domain\Artifact\Artifact> $artifacts
     *
     * @return array{0: RetrievedChunkCollection, 1: PerformanceMetricCollection}
     */
    private function measureRetrieval(array $artifacts, ChatQuestion $question): array
    {
        $metrics = PerformanceMetricCollection::empty();

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
            return [RetrievedChunkCollection::empty(), $metrics];
        }

        $embeddingTimer = new PerformanceTimer($this->clock);
        $embeddingTimer->start();
        $embeddedChunks = $this->embeddingGenerator->generate(new ChunkCollection($chunks));
        $metrics = $metrics->with($embeddingTimer->stop('embedding_ms'));

        if ($embeddedChunks->isEmpty()) {
            return [RetrievedChunkCollection::empty(), $metrics];
        }

        $indexTimer = new PerformanceTimer($this->clock);
        $indexTimer->start();
        $this->vectorStore->index($this->toVectorDocuments($embeddedChunks));
        $metrics = $metrics->with($indexTimer->stop('vector_index_ms'));

        $retrievalTimer = new PerformanceTimer($this->clock);
        $retrievalTimer->start();
        $retrievedChunks = $this->semanticRetriever->retrieve(
            $this->toSemanticQuery($question),
            $this->embeddingGenerator,
        );
        $metrics = $metrics->with($retrievalTimer->stop('retrieval_ms'));

        return [$retrievedChunks, $metrics];
    }

    private function complete(
        ChatAnswerResult $result,
        PerformanceMetricCollection $metrics,
        PerformanceTimer $totalTimer,
    ): ChatAnswerResult {
        $this->performanceMetricsRecorder->record(
            $metrics->with($totalTimer->stop('total_ms')),
        );

        return $result;
    }

    private function toSemanticQuery(ChatQuestion $question): SemanticQuery
    {
        $value = $question->value();

        if (strlen($value) > self::SEMANTIC_QUERY_MAX_LENGTH) {
            $value = substr($value, 0, self::SEMANTIC_QUERY_MAX_LENGTH);
        }

        return new SemanticQuery($value);
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
