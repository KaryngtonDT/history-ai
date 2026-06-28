<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Recommendation;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Recommendation\RecommendationReason;
use App\Domain\Recommendation\RecommendedArtifact;
use PHPUnit\Framework\TestCase;

final class RecommendedArtifactTest extends TestCase
{
    public function testExposesArtifactMetadataAndReason(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440002');

        $recommended = new RecommendedArtifact(
            artifactId: $artifactId,
            artifactType: ArtifactType::Summary,
            title: 'Summary',
            reason: RecommendationReason::DerivedFrom,
        );

        self::assertTrue($recommended->artifactId()->equals($artifactId));
        self::assertSame(ArtifactType::Summary, $recommended->artifactType());
        self::assertSame('Summary', $recommended->title());
        self::assertSame(RecommendationReason::DerivedFrom, $recommended->reason());
    }
}
