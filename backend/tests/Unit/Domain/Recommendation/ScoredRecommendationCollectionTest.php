<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Recommendation;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Recommendation\RecommendationReason;
use App\Domain\Recommendation\RecommendationScore;
use App\Domain\Recommendation\RecommendedArtifact;
use App\Domain\Recommendation\ScoredRecommendation;
use App\Domain\Recommendation\ScoredRecommendationCollection;
use PHPUnit\Framework\TestCase;

final class ScoredRecommendationCollectionTest extends TestCase
{
    public function testAllowsEmptyCollection(): void
    {
        $collection = ScoredRecommendationCollection::empty();

        self::assertTrue($collection->isEmpty());
        self::assertSame(0, $collection->count());
        self::assertSame([], $collection->recommendations());
    }

    public function testPreservesInsertionOrder(): void
    {
        $collection = new ScoredRecommendationCollection([
            $this->createScoredRecommendation(
                '550e8400-e29b-41d4-a716-446655440001',
                'Transcript',
                100,
            ),
            $this->createScoredRecommendation(
                '550e8400-e29b-41d4-a716-446655440002',
                'Summary',
                80,
            ),
        ]);

        self::assertSame(
            ['Transcript', 'Summary'],
            array_map(
                static fn (ScoredRecommendation $scored): string => $scored->recommendation()->title(),
                $collection->recommendations(),
            ),
        );
    }

    public function testReturnedRecommendationsDoNotMutateCollection(): void
    {
        $collection = new ScoredRecommendationCollection([
            $this->createScoredRecommendation(
                '550e8400-e29b-41d4-a716-446655440001',
                'Transcript',
                100,
            ),
        ]);

        $recommendations = $collection->recommendations();
        $recommendations[] = $this->createScoredRecommendation(
            '550e8400-e29b-41d4-a716-446655440002',
            'Summary',
            80,
        );

        self::assertSame(1, $collection->count());
    }

    private function createScoredRecommendation(
        string $artifactId,
        string $title,
        int $score,
    ): ScoredRecommendation {
        return new ScoredRecommendation(
            new RecommendedArtifact(
                artifactId: new ArtifactId($artifactId),
                artifactType: ArtifactType::Summary,
                title: $title,
                reason: RecommendationReason::Related,
            ),
            new RecommendationScore($score),
        );
    }
}
