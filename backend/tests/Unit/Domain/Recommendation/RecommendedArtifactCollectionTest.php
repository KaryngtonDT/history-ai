<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Recommendation;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Recommendation\RecommendationReason;
use App\Domain\Recommendation\RecommendedArtifact;
use App\Domain\Recommendation\RecommendedArtifactCollection;
use PHPUnit\Framework\TestCase;

final class RecommendedArtifactCollectionTest extends TestCase
{
    public function testAllowsEmptyCollection(): void
    {
        $collection = RecommendedArtifactCollection::empty();

        self::assertTrue($collection->isEmpty());
        self::assertSame(0, $collection->count());
        self::assertSame([], $collection->recommendations());
    }

    public function testPreservesInsertionOrder(): void
    {
        $first = $this->createRecommendation(
            '550e8400-e29b-41d4-a716-446655440001',
            ArtifactType::Transcript,
            'Transcript',
            RecommendationReason::Related,
        );
        $second = $this->createRecommendation(
            '550e8400-e29b-41d4-a716-446655440002',
            ArtifactType::Summary,
            'Summary',
            RecommendationReason::DerivedFrom,
        );
        $third = $this->createRecommendation(
            '550e8400-e29b-41d4-a716-446655440003',
            ArtifactType::Quiz,
            'Quiz',
            RecommendationReason::References,
        );

        $collection = new RecommendedArtifactCollection([$first, $second, $third]);

        self::assertSame(3, $collection->count());
        self::assertSame(
            ['Transcript', 'Summary', 'Quiz'],
            array_map(
                static fn (RecommendedArtifact $recommendation): string => $recommendation->title(),
                $collection->recommendations(),
            ),
        );
    }

    public function testReturnedRecommendationsDoNotMutateCollection(): void
    {
        $collection = new RecommendedArtifactCollection([
            $this->createRecommendation(
                '550e8400-e29b-41d4-a716-446655440001',
                ArtifactType::Transcript,
                'Transcript',
                RecommendationReason::Related,
            ),
        ]);

        $recommendations = $collection->recommendations();
        $recommendations[] = $this->createRecommendation(
            '550e8400-e29b-41d4-a716-446655440002',
            ArtifactType::Summary,
            'Summary',
            RecommendationReason::DerivedFrom,
        );

        self::assertSame(1, $collection->count());
    }

    private function createRecommendation(
        string $artifactId,
        ArtifactType $artifactType,
        string $title,
        RecommendationReason $reason,
    ): RecommendedArtifact {
        return new RecommendedArtifact(
            artifactId: new ArtifactId($artifactId),
            artifactType: $artifactType,
            title: $title,
            reason: $reason,
        );
    }
}
