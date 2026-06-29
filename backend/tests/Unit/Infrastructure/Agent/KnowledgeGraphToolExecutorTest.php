<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Agent;

use App\Application\Graph\Handlers\GetKnowledgeGraphHandler;
use App\Domain\Agent\AgentTool;
use App\Domain\Agent\AgentToolExecution;
use App\Domain\Agent\AgentToolExecutorInterface;
use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\Processing\ProcessingJobId;
use App\Infrastructure\Agent\KnowledgeGraphToolExecutor;
use PHPUnit\Framework\TestCase;

final class KnowledgeGraphToolExecutorTest extends TestCase
{
    public function testImplementsAgentToolExecutorInterface(): void
    {
        $handler = new GetKnowledgeGraphHandler($this->createMock(ArtifactRepositoryInterface::class));

        self::assertInstanceOf(
            AgentToolExecutorInterface::class,
            new KnowledgeGraphToolExecutor($handler),
        );
    }

    public function testExecuteCallsGetKnowledgeGraphHandler(): void
    {
        $contentId = ContentId::generate();
        $artifacts = [
            $this->createArtifact('550e8400-e29b-41d4-a716-446655440001', $contentId, ArtifactType::Transcript),
            $this->createArtifact('550e8400-e29b-41d4-a716-446655440002', $contentId, ArtifactType::Summary),
            $this->createArtifact('550e8400-e29b-41d4-a716-446655440003', $contentId, ArtifactType::Quiz),
        ];

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findByContentId')
            ->with(self::callback(static fn (ContentId $id): bool => $id->equals($contentId)))
            ->willReturn($artifacts);

        $execution = new AgentToolExecution(
            AgentTool::KnowledgeGraph,
            'Compare Rome versus Byzantium',
            $contentId->value,
        );

        $result = (new KnowledgeGraphToolExecutor(new GetKnowledgeGraphHandler($repository)))->execute($execution);

        self::assertSame(AgentTool::KnowledgeGraph, $result->tool());
        self::assertSame('Knowledge graph contains 3 nodes and 3 relationships.', $result->summary());
        self::assertSame(
            ['nodeCount' => 3, 'edgeCount' => 3],
            $result->metadata(),
        );
    }

    public function testExecuteReturnsEmptyGraphSummaryAndMetadata(): void
    {
        $contentId = ContentId::generate();

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findByContentId')
            ->willReturn([]);

        $execution = new AgentToolExecution(
            AgentTool::KnowledgeGraph,
            'Compare Rome versus Byzantium',
            $contentId->value,
        );

        $result = (new KnowledgeGraphToolExecutor(new GetKnowledgeGraphHandler($repository)))->execute($execution);

        self::assertSame('Knowledge graph is empty.', $result->summary());
        self::assertSame(['nodeCount' => 0, 'edgeCount' => 0], $result->metadata());
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
}
