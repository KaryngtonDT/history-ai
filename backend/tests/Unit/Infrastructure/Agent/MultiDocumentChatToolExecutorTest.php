<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Agent;

use App\Application\Chat\ContentChatAnswerer;
use App\Application\Chat\Handlers\AskConversationChatHandler;
use App\Domain\Agent\AgentTool;
use App\Domain\Agent\AgentToolExecution;
use App\Domain\Agent\AgentToolExecutorInterface;
use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Chat\ChatOrchestrator;
use App\Domain\Chat\Conversation;
use App\Domain\Chat\ConversationId;
use App\Domain\Chat\ConversationRepositoryInterface;
use App\Domain\Content\ContentId;
use App\Domain\Platform\CorrelationId;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Semantic\Chunker;
use App\Domain\Semantic\SemanticRetriever;
use App\Infrastructure\Agent\MultiDocumentChatToolExecutor;
use App\Infrastructure\Chat\MockChatProvider;
use App\Infrastructure\Semantic\DeterministicEmbeddingGenerator;
use App\Infrastructure\Semantic\DeterministicEmbeddingProvider;
use App\Infrastructure\Semantic\InMemoryVectorStore;
use App\Tests\Unit\Application\Platform\Support\FixedClock;
use App\Tests\Unit\Application\Platform\Support\FixedRequestContextProvider;
use App\Tests\Unit\Application\Platform\Support\RecordingPerformanceMetricsRecorder;
use App\Tests\Unit\Application\Platform\Support\RecordingPlatformLogger;
use PHPUnit\Framework\TestCase;

final class MultiDocumentChatToolExecutorTest extends TestCase
{
    private const string CONTENT_ID = '550e8400-e29b-41d4-a716-446655440000';
    private const string OTHER_CONTENT_ID = '550e8400-e29b-41d4-a716-446655440099';
    private const string CONVERSATION_ID = '550e8400-e29b-41d4-a716-446655440001';

    public function testImplementsAgentToolExecutorInterface(): void
    {
        $handler = $this->createHandler(
            $this->createStub(ConversationRepositoryInterface::class),
            $this->createStub(ArtifactRepositoryInterface::class),
        );

        self::assertInstanceOf(
            AgentToolExecutorInterface::class,
            new MultiDocumentChatToolExecutor($handler),
        );
    }

    public function testExecuteReturnsRequiresConversationWhenConversationIdMissing(): void
    {
        $execution = new AgentToolExecution(
            AgentTool::MultiDocumentChat,
            'What is Rome?',
            self::CONTENT_ID,
        );

        $conversationRepository = $this->createMock(ConversationRepositoryInterface::class);
        $conversationRepository->expects(self::never())->method('findById');

        $result = (new MultiDocumentChatToolExecutor(
            $this->createHandler(
                $conversationRepository,
                $this->createStub(ArtifactRepositoryInterface::class),
            ),
        ))->execute($execution);

        self::assertSame(AgentTool::MultiDocumentChat, $result->tool());
        self::assertSame('Multi-document chat requires a conversation.', $result->summary());
        self::assertSame(['requiresConversation' => true], $result->metadata());
    }

    public function testExecuteReturnsRequiresConversationWhenConversationIdBlank(): void
    {
        $execution = new AgentToolExecution(
            AgentTool::MultiDocumentChat,
            'What is Rome?',
            self::CONTENT_ID,
            '   ',
        );

        $conversationRepository = $this->createMock(ConversationRepositoryInterface::class);
        $conversationRepository->expects(self::never())->method('findById');

        $result = (new MultiDocumentChatToolExecutor(
            $this->createHandler(
                $conversationRepository,
                $this->createStub(ArtifactRepositoryInterface::class),
            ),
        ))->execute($execution);

        self::assertSame('Multi-document chat requires a conversation.', $result->summary());
        self::assertSame(['requiresConversation' => true], $result->metadata());
    }

    public function testExecuteCallsAskConversationChatHandler(): void
    {
        $conversationId = new ConversationId(self::CONVERSATION_ID);
        $contentId = new ContentId(self::CONTENT_ID);

        $conversationRepository = $this->createMock(ConversationRepositoryInterface::class);
        $conversationRepository
            ->expects(self::once())
            ->method('findById')
            ->with($conversationId)
            ->willReturn(null);
        $conversationRepository->expects(self::once())->method('save');

        $artifactRepository = $this->createMock(ArtifactRepositoryInterface::class);
        $artifactRepository
            ->expects(self::once())
            ->method('findByContentId')
            ->with($contentId)
            ->willReturn([]);

        $execution = new AgentToolExecution(
            AgentTool::MultiDocumentChat,
            'Why did Rome fall?',
            self::CONTENT_ID,
            self::CONVERSATION_ID,
        );

        $result = (new MultiDocumentChatToolExecutor(
            $this->createHandler($conversationRepository, $artifactRepository),
        ))->execute($execution);

        self::assertSame('Multi-document chat generated an answer.', $result->summary());
        self::assertSame(
            [
                'messageCount' => 2,
                'sourceCount' => 0,
                'citationCount' => 0,
            ],
            $result->metadata(),
        );
    }

    public function testExecuteReturnsMessageSourceAndCitationMetadata(): void
    {
        $conversationId = new ConversationId(self::CONVERSATION_ID);
        $existing = Conversation::start($conversationId, new ContentId(self::CONTENT_ID))
            ->addDocument(new ContentId(self::OTHER_CONTENT_ID));

        $primaryArtifact = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440002',
            self::CONTENT_ID,
            'Primary artifact content',
        );
        $secondaryArtifact = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440003',
            self::OTHER_CONTENT_ID,
            'Secondary artifact content',
        );

        $conversationRepository = $this->createMock(ConversationRepositoryInterface::class);
        $conversationRepository->method('findById')->willReturn($existing);
        $conversationRepository->expects(self::once())->method('save');

        $artifactRepository = $this->createStub(ArtifactRepositoryInterface::class);
        $artifactRepository
            ->method('findByContentId')
            ->willReturnCallback(static function (ContentId $contentId) use (
                $primaryArtifact,
                $secondaryArtifact,
            ): array {
                return match ($contentId->value) {
                    self::CONTENT_ID => [$primaryArtifact],
                    self::OTHER_CONTENT_ID => [$secondaryArtifact],
                    default => [],
                };
            });

        $execution = new AgentToolExecution(
            AgentTool::MultiDocumentChat,
            'Primary artifact content',
            self::CONTENT_ID,
            self::CONVERSATION_ID,
        );

        $result = (new MultiDocumentChatToolExecutor(
            $this->createHandler($conversationRepository, $artifactRepository),
        ))->execute($execution);

        self::assertSame('Multi-document chat generated an answer.', $result->summary());
        self::assertSame(2, $result->metadata()['messageCount']);
        self::assertSame(2, $result->metadata()['sourceCount']);
        self::assertGreaterThanOrEqual(0, $result->metadata()['citationCount']);
    }

    private function createHandler(
        ConversationRepositoryInterface $conversationRepository,
        ArtifactRepositoryInterface $artifactRepository,
    ): AskConversationChatHandler {
        return new AskConversationChatHandler(
            $conversationRepository,
            $artifactRepository,
            $this->createContentChatAnswerer(),
        );
    }

    private function createContentChatAnswerer(): ContentChatAnswerer
    {
        $vectorStore = new InMemoryVectorStore();
        $contextProvider = new FixedRequestContextProvider(new CorrelationId('c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d'));

        return new ContentChatAnswerer(
            new Chunker(),
            new DeterministicEmbeddingGenerator(new DeterministicEmbeddingProvider()),
            $vectorStore,
            new SemanticRetriever($vectorStore),
            new ChatOrchestrator(),
            new MockChatProvider(),
            new RecordingPlatformLogger($contextProvider),
            new RecordingPerformanceMetricsRecorder(),
            new FixedClock(),
        );
    }

    private function createArtifact(string $id, string $contentId, string $content): Artifact
    {
        return Artifact::create(
            new ArtifactId($id),
            new ContentId($contentId),
            ProcessingJobId::generate(),
            ArtifactType::Summary,
            ArtifactContent::fromString($content),
        );
    }
}
