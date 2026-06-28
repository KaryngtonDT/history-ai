<?php

declare(strict_types=1);

namespace App\Application\Chat\Handlers;

use App\Application\Chat\Commands\AskContentChatStreamCommand;
use App\Application\Chat\DTO\ChatStreamResult;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Chat\ChatContext;
use App\Domain\Chat\ChatOrchestrator;
use App\Domain\Chat\ChatProviderOptions;
use App\Domain\Chat\ChatQuestion;
use App\Domain\Chat\ChatRequest;
use App\Domain\Chat\StreamingChatProviderInterface;
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

final class AskContentChatStreamHandler
{
    private const int SEMANTIC_QUERY_MAX_LENGTH = 500;

    public function __construct(
        private readonly ArtifactRepositoryInterface $artifactRepository,
        private readonly Chunker $chunker,
        private readonly EmbeddingGeneratorInterface $embeddingGenerator,
        private readonly VectorStoreInterface $vectorStore,
        private readonly SemanticRetriever $semanticRetriever,
        private readonly ChatOrchestrator $chatOrchestrator,
        private readonly StreamingChatProviderInterface $streamingChatProvider,
    ) {
    }

    public function __invoke(AskContentChatStreamCommand $command): ChatStreamResult
    {
        $question = new ChatQuestion($command->question);
        $artifacts = $this->artifactRepository->findByContentId(
            new ContentId($command->contentId),
        );

        $retrievedChunks = [] === $artifacts
            ? RetrievedChunkCollection::empty()
            : $this->retrieveChunks($artifacts, $question);

        $context = new ChatContext($question, $retrievedChunks);
        $prompt = $this->chatOrchestrator->buildPrompt($context);
        $stream = $this->streamingChatProvider->stream(ChatRequest::create(
            $prompt,
            $context->sources(),
            ChatProviderOptions::defaults(),
        ));

        return ChatStreamResult::fromDomain($stream);
    }

    /**
     * @param list<\App\Domain\Artifact\Artifact> $artifacts
     */
    private function retrieveChunks(array $artifacts, ChatQuestion $question): RetrievedChunkCollection
    {
        /** @var list<\App\Domain\Semantic\Chunk> $chunks */
        $chunks = [];

        foreach ($artifacts as $artifact) {
            foreach ($this->chunker->chunk($artifact)->chunks() as $chunk) {
                $chunks[] = $chunk;
            }
        }

        if ([] === $chunks) {
            return RetrievedChunkCollection::empty();
        }

        $embeddedChunks = $this->embeddingGenerator->generate(new ChunkCollection($chunks));

        if ($embeddedChunks->isEmpty()) {
            return RetrievedChunkCollection::empty();
        }

        $this->vectorStore->index($this->toVectorDocuments($embeddedChunks));

        return $this->semanticRetriever->retrieve(
            $this->toSemanticQuery($question),
            $this->embeddingGenerator,
        );
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
