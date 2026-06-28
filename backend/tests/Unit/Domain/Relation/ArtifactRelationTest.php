<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Relation;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Relation\ArtifactRelation;
use App\Domain\Relation\ArtifactRelationType;
use App\Domain\Relation\Exception\InvalidArtifactRelationException;
use PHPUnit\Framework\TestCase;

final class ArtifactRelationTest extends TestCase
{
    public function testCreatesValidRelationBetweenDistinctArtifacts(): void
    {
        $sourceId = new ArtifactId('550e8400-e29b-41d4-a716-446655440000');
        $targetId = new ArtifactId('6ba7b810-9dad-11d1-80b4-00c04fd430c8');

        $relation = new ArtifactRelation(
            $sourceId,
            $targetId,
            ArtifactRelationType::DerivedFrom,
        );

        self::assertTrue($relation->sourceArtifactId()->equals($sourceId));
        self::assertTrue($relation->targetArtifactId()->equals($targetId));
        self::assertSame(ArtifactRelationType::DerivedFrom, $relation->relationType());
    }

    public function testRejectsSelfReferencingRelation(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440000');

        $this->expectException(InvalidArtifactRelationException::class);
        $this->expectExceptionMessage(
            'An artifact relation cannot reference the same artifact as both source and target.',
        );

        new ArtifactRelation(
            $artifactId,
            $artifactId,
            ArtifactRelationType::Related,
        );
    }
}
