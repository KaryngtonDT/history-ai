<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Graph;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Graph\Exception\InvalidKnowledgeGraphException;
use App\Domain\Graph\GraphEdge;
use App\Domain\Relation\ArtifactRelationType;
use PHPUnit\Framework\TestCase;

final class GraphEdgeTest extends TestCase
{
    public function testExposesSourceTargetRelationTypeAndWeight(): void
    {
        $sourceId = new ArtifactId('550e8400-e29b-41d4-a716-446655440000');
        $targetId = new ArtifactId('6ba7b810-9dad-11d1-80b4-00c04fd430c8');

        $edge = new GraphEdge($sourceId, $targetId, ArtifactRelationType::DerivedFrom, 0.75);

        self::assertTrue($edge->sourceArtifactId()->equals($sourceId));
        self::assertTrue($edge->targetArtifactId()->equals($targetId));
        self::assertSame(ArtifactRelationType::DerivedFrom, $edge->relationType());
        self::assertSame(0.75, $edge->weight());
    }

    public function testDefaultsWeightToOne(): void
    {
        $edge = new GraphEdge(
            new ArtifactId('550e8400-e29b-41d4-a716-446655440000'),
            new ArtifactId('6ba7b810-9dad-11d1-80b4-00c04fd430c8'),
            ArtifactRelationType::Related,
        );

        self::assertSame(1.0, $edge->weight());
    }

    public function testRejectsNegativeWeight(): void
    {
        $this->expectException(InvalidKnowledgeGraphException::class);
        $this->expectExceptionMessage('Graph edge weight must be greater than or equal to 0.');

        new GraphEdge(
            new ArtifactId('550e8400-e29b-41d4-a716-446655440000'),
            new ArtifactId('6ba7b810-9dad-11d1-80b4-00c04fd430c8'),
            ArtifactRelationType::DerivedFrom,
            -0.1,
        );
    }
}
