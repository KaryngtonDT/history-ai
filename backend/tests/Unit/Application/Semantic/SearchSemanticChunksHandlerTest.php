<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Semantic;

use App\Application\Semantic\Handlers\SearchSemanticChunksHandler;
use App\Application\Semantic\Queries\SearchSemanticChunksQuery;
use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Semantic\Chunker;
use App\Domain\Semantic\SemanticRetriever;
use App\Infrastructure\Semantic\DeterministicEmbeddingGenerator;
use PHPUnit\Framework\TestCase;

final class SearchSemanticChunksHandlerTest extends TestCase
{
    private function createHandler(ArtifactRepositoryInterface $repository): SearchSemanticChunksHandler
    {
        return new SearchSemanticChunksHandler(
            $repository,
            new Chunker(),
            new DeterministicEmbeddingGenerator(),
            new SemanticRetriever(),
        );
    }

    public function testReturnsEmptyResultsWhenNoArtifactsExist(): void
    {
        $contentId = ContentId::generate();

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findByContentId')
            ->with(self::callback(static fn (ContentId $id): bool => $id->equals($contentId)))
            ->willReturn([]);

        $handler = $this->createHandler($repository);
        $result = $handler(new SearchSemanticChunksQuery($contentId->value, 'rome'));

        self::assertSame([], $result->results);
    }

    public function testReturnsSemanticResultsOrderedByDescendingScore(): void
    {
        $contentId = ContentId::generate();
        $summaryId = '550e8400-e29b-41d4-a716-446655440002';
        $timelineId = '550e8400-e29b-41d4-a716-446655440004';

        $queryText = "## Ancient Rome\n753 BC — Foundation of Rome";
        $artifacts = [
            $this->createArtifact(
                $summaryId,
                $contentId,
                ArtifactType::Summary,
                $queryText,
            ),
            $this->createArtifact(
                $timelineId,
                $contentId,
                ArtifactType::Timeline,
                "## Greek history\nClassical period overview",
            ),
        ];

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findByContentId')
            ->willReturn($artifacts);

        $handler = $this->createHandler($repository);
        $result = $handler(new SearchSemanticChunksQuery(
            $contentId->value,
            $queryText,
        ));

        self::assertNotSame([], $result->results);
        self::assertSame($summaryId, $result->results[0]->artifactId);
        self::assertSame(0, $result->results[0]->position);
        self::assertSame($queryText, $result->results[0]->text);
        self::assertSame(1.0, $result->results[0]->score);

        $scores = array_map(
            static fn (object $item): float => $item->score,
            $result->results,
        );

        for ($index = 0; $index < count($scores) - 1; ++$index) {
            self::assertGreaterThanOrEqual($scores[$index + 1], $scores[$index]);
        }
    }

    public function testMapsRetrievedChunkFields(): void
    {
        $contentId = ContentId::generate();
        $summaryId = '550e8400-e29b-41d4-a716-446655440002';
        $artifacts = [
            $this->createArtifact(
                $summaryId,
                $contentId,
                ArtifactType::Summary,
                "## Ancient Rome\n753 BC — Foundation of Rome",
            ),
        ];

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findByContentId')
            ->willReturn($artifacts);

        $handler = $this->createHandler($repository);
        $result = $handler(new SearchSemanticChunksQuery($contentId->value, 'Ancient Rome'));

        self::assertCount(1, $result->results);
        self::assertSame($summaryId, $result->results[0]->artifactId);
        self::assertNotSame('', $result->results[0]->chunkId);
        self::assertSame(0, $result->results[0]->position);
        self::assertStringContainsString('Ancient Rome', $result->results[0]->text);
        self::assertGreaterThanOrEqual(0.0, $result->results[0]->score);
        self::assertLessThanOrEqual(1.0, $result->results[0]->score);
    }

    private function createArtifact(
        string $id,
        ContentId $contentId,
        ArtifactType $type,
        string $content,
    ): Artifact {
        return Artifact::create(
            new ArtifactId($id),
            $contentId,
            ProcessingJobId::generate(),
            $type,
            ArtifactContent::fromString($content),
        );
    }
}
