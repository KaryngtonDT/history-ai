<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Relation;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Relation\ArtifactRelation;
use App\Domain\Relation\ArtifactRelationCollection;
use App\Domain\Relation\ArtifactRelationType;
use PHPUnit\Framework\TestCase;

final class ArtifactRelationCollectionTest extends TestCase
{
    public function testAllowsEmptyCollection(): void
    {
        $collection = ArtifactRelationCollection::empty();

        self::assertTrue($collection->isEmpty());
        self::assertSame(0, $collection->count());
        self::assertSame([], $collection->relations());
    }

    public function testPreservesInsertionOrder(): void
    {
        $first = $this->createRelation(
            '550e8400-e29b-41d4-a716-446655440000',
            '6ba7b810-9dad-11d1-80b4-00c04fd430c8',
            ArtifactRelationType::DerivedFrom,
        );
        $second = $this->createRelation(
            '6ba7b810-9dad-11d1-80b4-00c04fd430c8',
            '7c9e6679-7425-40de-944b-e07fc1f90ae7',
            ArtifactRelationType::Next,
        );
        $third = $this->createRelation(
            '7c9e6679-7425-40de-944b-e07fc1f90ae7',
            '550e8400-e29b-41d4-a716-446655440000',
            ArtifactRelationType::References,
        );

        $collection = new ArtifactRelationCollection([$first, $second, $third]);

        self::assertSame(3, $collection->count());
        self::assertSame(
            [
                ArtifactRelationType::DerivedFrom,
                ArtifactRelationType::Next,
                ArtifactRelationType::References,
            ],
            array_map(
                static fn (ArtifactRelation $relation): ArtifactRelationType => $relation->relationType(),
                $collection->relations(),
            ),
        );
    }

    public function testReturnedRelationsDoNotMutateCollection(): void
    {
        $collection = new ArtifactRelationCollection([
            $this->createRelation(
                '550e8400-e29b-41d4-a716-446655440000',
                '6ba7b810-9dad-11d1-80b4-00c04fd430c8',
                ArtifactRelationType::Related,
            ),
        ]);

        $relations = $collection->relations();
        $relations[] = $this->createRelation(
            '6ba7b810-9dad-11d1-80b4-00c04fd430c8',
            '7c9e6679-7425-40de-944b-e07fc1f90ae7',
            ArtifactRelationType::Previous,
        );

        self::assertSame(1, $collection->count());
        self::assertSame(
            [ArtifactRelationType::Related],
            array_map(
                static fn (ArtifactRelation $relation): ArtifactRelationType => $relation->relationType(),
                $collection->relations(),
            ),
        );
    }

    public function testReindexesRelationsToPreserveListSemantics(): void
    {
        $collection = new ArtifactRelationCollection([
            2 => $this->createRelation(
                '550e8400-e29b-41d4-a716-446655440000',
                '6ba7b810-9dad-11d1-80b4-00c04fd430c8',
                ArtifactRelationType::Related,
            ),
            5 => $this->createRelation(
                '6ba7b810-9dad-11d1-80b4-00c04fd430c8',
                '7c9e6679-7425-40de-944b-e07fc1f90ae7',
                ArtifactRelationType::Next,
            ),
        ]);

        self::assertSame([0, 1], array_keys($collection->relations()));
    }

    private function createRelation(
        string $sourceId,
        string $targetId,
        ArtifactRelationType $type,
    ): ArtifactRelation {
        return new ArtifactRelation(
            new ArtifactId($sourceId),
            new ArtifactId($targetId),
            $type,
        );
    }
}
