<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Graph;

use App\Application\Graph\Handlers\GetConversationKnowledgeGraphHandler;
use App\Application\Graph\Queries\GetConversationKnowledgeGraphQuery;
use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Chat\Conversation;
use App\Domain\Chat\ConversationId;
use App\Domain\Chat\ConversationRepositoryInterface;
use App\Domain\Chat\Exception\ConversationNotFoundException;
use App\Domain\Chat\SelectedDocument;
use App\Domain\Chat\SelectedDocumentCollection;
use App\Domain\Content\ContentId;
use App\Domain\Processing\ProcessingJobId;
use PHPUnit\Framework\TestCase;

final class GetConversationKnowledgeGraphHandlerTest extends TestCase
{
    public function testThrowsWhenConversationIsMissing(): void
    {
        $conversationRepository = $this->createMock(ConversationRepositoryInterface::class);
        $conversationRepository
            ->expects(self::once())
            ->method('findById')
            ->willReturn(null);

        $artifactRepository = $this->createMock(ArtifactRepositoryInterface::class);
        $artifactRepository->expects(self::never())->method('findByContentId');

        $handler = new GetConversationKnowledgeGraphHandler(
            $conversationRepository,
            $artifactRepository,
        );

        $this->expectException(ConversationNotFoundException::class);

        $handler(new GetConversationKnowledgeGraphQuery('550e8400-e29b-41d4-a716-446655440001'));
    }

    public function testBuildsGraphFromAllSelectedDocumentsInOrder(): void
    {
        $conversationId = '550e8400-e29b-41d4-a716-446655440001';
        $firstContentId = ContentId::generate();
        $secondContentId = ContentId::generate();
        $conversation = new Conversation(
            new ConversationId($conversationId),
            new SelectedDocumentCollection([
                new SelectedDocument($firstContentId),
                new SelectedDocument($secondContentId),
            ]),
        );

        $firstTranscriptId = '550e8400-e29b-41d4-a716-446655440001';
        $firstSummaryId = '550e8400-e29b-41d4-a716-446655440002';
        $secondQuizId = '550e8400-e29b-41d4-a716-446655440003';

        $conversationRepository = $this->createMock(ConversationRepositoryInterface::class);
        $conversationRepository
            ->expects(self::once())
            ->method('findById')
            ->willReturn($conversation);

        $artifactRepository = $this->createMock(ArtifactRepositoryInterface::class);
        $artifactRepository
            ->expects(self::exactly(2))
            ->method('findByContentId')
            ->willReturnCallback(function (ContentId $contentId) use (
                $firstContentId,
                $secondContentId,
                $firstTranscriptId,
                $firstSummaryId,
                $secondQuizId,
            ): array {
                if ($contentId->equals($firstContentId)) {
                    return [
                        $this->createArtifact($firstTranscriptId, $firstContentId, ArtifactType::Transcript),
                        $this->createArtifact($firstSummaryId, $firstContentId, ArtifactType::Summary),
                    ];
                }

                if ($contentId->equals($secondContentId)) {
                    return [
                        $this->createArtifact($secondQuizId, $secondContentId, ArtifactType::Quiz),
                    ];
                }

                return [];
            });

        $handler = new GetConversationKnowledgeGraphHandler(
            $conversationRepository,
            $artifactRepository,
        );

        $result = $handler(new GetConversationKnowledgeGraphQuery($conversationId));

        self::assertSame(
            [$firstTranscriptId, $firstSummaryId, $secondQuizId],
            array_map(static fn (object $node): string => $node->artifactId, $result->nodes),
        );
        self::assertTrue($this->containsEdge(
            $result->edges,
            $firstSummaryId,
            $firstTranscriptId,
            'derived_from',
        ));
        self::assertTrue($this->containsEdge(
            $result->edges,
            $secondQuizId,
            $firstSummaryId,
            'references',
        ));
    }

    public function testReturnsEmptyGraphWhenSelectedDocumentsHaveNoArtifacts(): void
    {
        $conversationId = '550e8400-e29b-41d4-a716-446655440001';
        $contentId = ContentId::generate();
        $conversation = Conversation::start(new ConversationId($conversationId), $contentId);

        $conversationRepository = $this->createMock(ConversationRepositoryInterface::class);
        $conversationRepository
            ->expects(self::once())
            ->method('findById')
            ->willReturn($conversation);

        $artifactRepository = $this->createMock(ArtifactRepositoryInterface::class);
        $artifactRepository
            ->expects(self::once())
            ->method('findByContentId')
            ->willReturn([]);

        $handler = new GetConversationKnowledgeGraphHandler(
            $conversationRepository,
            $artifactRepository,
        );

        $result = $handler(new GetConversationKnowledgeGraphQuery($conversationId));

        self::assertSame([], $result->nodes);
        self::assertSame([], $result->edges);
    }

    private function createArtifact(string $id, ContentId $contentId, ArtifactType $type): Artifact
    {
        return Artifact::create(
            new ArtifactId($id),
            $contentId,
            ProcessingJobId::generate(),
            $type,
            ArtifactContent::fromString('content for ' . $type->value),
        );
    }

    /**
     * @param list<object{sourceArtifactId: string, targetArtifactId: string, type: string}> $edges
     */
    private function containsEdge(
        array $edges,
        string $sourceId,
        string $targetId,
        string $type,
    ): bool {
        foreach ($edges as $edge) {
            if (
                $edge->sourceArtifactId === $sourceId
                && $edge->targetArtifactId === $targetId
                && $edge->type === $type
            ) {
                return true;
            }
        }

        return false;
    }
}
