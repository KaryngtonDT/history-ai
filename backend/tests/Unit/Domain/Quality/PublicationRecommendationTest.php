<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Quality;

use App\Domain\Quality\PublicationRecommendation;
use App\Domain\Quality\QualityReport;
use App\Domain\Quality\QualityScore;
use PHPUnit\Framework\TestCase;

final class PublicationRecommendationTest extends TestCase
{
    public function testRecommendationValues(): void
    {
        self::assertSame('ready', PublicationRecommendation::Ready->value);
        self::assertSame('review_recommended', PublicationRecommendation::ReviewRecommended->value);
        self::assertSame('regenerate_required', PublicationRecommendation::RegenerateRequired->value);
    }

    public function testRecommendationMapsFromOverallScore(): void
    {
        self::assertSame(
            PublicationRecommendation::Ready,
            QualityReport::recommendationFor(QualityScore::create(100)),
        );
        self::assertSame(
            PublicationRecommendation::RegenerateRequired,
            QualityReport::recommendationFor(QualityScore::create(50)),
        );
    }
}
