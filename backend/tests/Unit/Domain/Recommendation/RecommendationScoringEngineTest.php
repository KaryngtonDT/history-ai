<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Recommendation;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Recommendation\RecommendationReason;
use App\Domain\Recommendation\RecommendationScoringEngine;
use App\Domain\Recommendation\RecommendationWeight;
use App\Domain\Recommendation\RecommendedArtifact;
use App\Domain\Recommendation\RecommendedArtifactCollection;
use App\Domain\Recommendation\ScoredRecommendation;
use PHPUnit\Framework\TestCase;

final class RecommendationScoringEngineTest extends TestCase
{
    private RecommendationScoringEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new RecommendationScoringEngine();
    }

    public function testEmptyCollectionReturnsEmptyScoredCollection(): void
    {
        $result = $this->engine->score(RecommendedArtifactCollection::empty());

        self::assertTrue($result->isEmpty());
    }

    public function testSortsByDescendingScore(): void
    {
        $collection = new RecommendedArtifactCollection([
            $this->createRecommendation(
                '550e8400-e29b-41d4-a716-446655440001',
                'Related artifact',
                RecommendationReason::Related,
            ),
            $this->createRecommendation(
                '550e8400-e29b-41d4-a716-446655440002',
                'Derived artifact',
                RecommendationReason::DerivedFrom,
            ),
            $this->createRecommendation(
                '550e8400-e29b-41d4-a716-446655440003',
                'Referenced artifact',
                RecommendationReason::References,
            ),
            $this->createRecommendation(
                '550e8400-e29b-41d4-a716-446655440004',
                'Next artifact',
                RecommendationReason::Next,
            ),
            $this->createRecommendation(
                '550e8400-e29b-41d4-a716-446655440005',
                'Previous artifact',
                RecommendationReason::Previous,
            ),
        ]);

        $result = $this->engine->score($collection);

        self::assertSame(
            [
                RecommendationWeight::DERIVED_FROM,
                RecommendationWeight::REFERENCES,
                RecommendationWeight::RELATED,
                RecommendationWeight::NEXT,
                RecommendationWeight::PREVIOUS,
            ],
            array_map(
                static fn (ScoredRecommendation $scored): int => $scored->score()->value(),
                $result->recommendations(),
            ),
        );
        self::assertSame(
            [
                'Derived artifact',
                'Referenced artifact',
                'Related artifact',
                'Next artifact',
                'Previous artifact',
            ],
            array_map(
                static fn (ScoredRecommendation $scored): string => $scored->recommendation()->title(),
                $result->recommendations(),
            ),
        );
    }

    public function testPreservesStableOrderingForEqualScores(): void
    {
        $collection = new RecommendedArtifactCollection([
            $this->createRecommendation(
                '550e8400-e29b-41d4-a716-446655440001',
                'First references',
                RecommendationReason::References,
            ),
            $this->createRecommendation(
                '550e8400-e29b-41d4-a716-446655440002',
                'Second references',
                RecommendationReason::References,
            ),
            $this->createRecommendation(
                '550e8400-e29b-41d4-a716-446655440003',
                'First next',
                RecommendationReason::Next,
            ),
            $this->createRecommendation(
                '550e8400-e29b-41d4-a716-446655440004',
                'Second next',
                RecommendationReason::Next,
            ),
        ]);

        $result = $this->engine->score($collection);

        self::assertSame(
            ['First references', 'Second references', 'First next', 'Second next'],
            array_map(
                static fn (ScoredRecommendation $scored): string => $scored->recommendation()->title(),
                $result->recommendations(),
            ),
        );
    }

    public function testRemovesDuplicateRecommendationsByArtifactId(): void
    {
        $collection = new RecommendedArtifactCollection([
            $this->createRecommendation(
                '550e8400-e29b-41d4-a716-446655440001',
                'Transcript',
                RecommendationReason::DerivedFrom,
            ),
            $this->createRecommendation(
                '550e8400-e29b-41d4-a716-446655440001',
                'Transcript duplicate',
                RecommendationReason::Related,
            ),
        ]);

        $result = $this->engine->score($collection);

        self::assertSame(1, $result->count());
        self::assertSame('Transcript', $result->recommendations()[0]->recommendation()->title());
        self::assertSame(
            RecommendationWeight::DERIVED_FROM,
            $result->recommendations()[0]->score()->value(),
        );
    }

    public function testMapsReasonToDefaultWeight(): void
    {
        $collection = new RecommendedArtifactCollection([
            $this->createRecommendation(
                '550e8400-e29b-41d4-a716-446655440001',
                'Derived',
                RecommendationReason::DerivedFrom,
            ),
        ]);

        $result = $this->engine->score($collection);

        self::assertSame(
            RecommendationWeight::DERIVED_FROM,
            $result->recommendations()[0]->score()->value(),
        );
    }

    private function createRecommendation(
        string $artifactId,
        string $title,
        RecommendationReason $reason,
    ): RecommendedArtifact {
        return new RecommendedArtifact(
            artifactId: new ArtifactId($artifactId),
            artifactType: ArtifactType::Summary,
            title: $title,
            reason: $reason,
        );
    }
}
