<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Recommendation;

use App\Application\Recommendation\DTO\RecommendedArtifactResult;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Recommendation\RecommendationReason;
use App\Domain\Recommendation\RecommendationScore;
use App\Domain\Recommendation\RecommendationWeight;
use App\Domain\Recommendation\RecommendedArtifact;
use App\Domain\Recommendation\ScoredRecommendation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RecommendedArtifactResultTest extends TestCase
{
    #[DataProvider('reasonScoreProvider')]
    public function testMapsScoredRecommendationReasonToExpectedScore(
        RecommendationReason $reason,
        int $expectedScore,
    ): void {
        $result = RecommendedArtifactResult::fromScoredDomain(
            new ScoredRecommendation(
                new RecommendedArtifact(
                    artifactId: new ArtifactId('550e8400-e29b-41d4-a716-446655440001'),
                    artifactType: ArtifactType::Summary,
                    title: 'Summary',
                    reason: $reason,
                ),
                new RecommendationScore($expectedScore),
            ),
        );

        self::assertSame($expectedScore, $result->score);
        self::assertSame($reason->value, $result->reason);
    }

    /**
     * @return list<array{0: RecommendationReason, 1: int}>
     */
    public static function reasonScoreProvider(): array
    {
        return [
            [RecommendationReason::DerivedFrom, RecommendationWeight::DERIVED_FROM],
            [RecommendationReason::References, RecommendationWeight::REFERENCES],
            [RecommendationReason::Related, RecommendationWeight::RELATED],
            [RecommendationReason::Next, RecommendationWeight::NEXT],
            [RecommendationReason::Previous, RecommendationWeight::PREVIOUS],
        ];
    }

    public function testFromDomainDoesNotSetScore(): void
    {
        $result = RecommendedArtifactResult::fromDomain(
            new RecommendedArtifact(
                artifactId: new ArtifactId('550e8400-e29b-41d4-a716-446655440001'),
                artifactType: ArtifactType::Summary,
                title: 'Summary',
                reason: RecommendationReason::Related,
            ),
        );

        self::assertNull($result->score);
    }
}
