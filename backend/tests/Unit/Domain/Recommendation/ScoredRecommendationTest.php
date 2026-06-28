<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Recommendation;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Recommendation\RecommendationReason;
use App\Domain\Recommendation\RecommendationScore;
use App\Domain\Recommendation\RecommendedArtifact;
use App\Domain\Recommendation\ScoredRecommendation;
use PHPUnit\Framework\TestCase;

final class ScoredRecommendationTest extends TestCase
{
    public function testExposesRecommendationAndScore(): void
    {
        $recommendation = new RecommendedArtifact(
            artifactId: new ArtifactId('550e8400-e29b-41d4-a716-446655440002'),
            artifactType: ArtifactType::Summary,
            title: 'Summary',
            reason: RecommendationReason::DerivedFrom,
        );
        $score = new RecommendationScore(100);

        $scored = new ScoredRecommendation($recommendation, $score);

        self::assertSame($recommendation, $scored->recommendation());
        self::assertSame($score, $scored->score());
        self::assertSame(100, $scored->score()->value());
        self::assertSame('Summary', $scored->recommendation()->title());
    }
}
