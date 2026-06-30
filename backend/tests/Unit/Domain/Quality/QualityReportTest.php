<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Quality;

use App\Domain\Quality\Exception\InvalidQualityReportException;
use App\Domain\Quality\PublicationRecommendation;
use App\Domain\Quality\QualityCategory;
use App\Domain\Quality\QualityMetric;
use App\Domain\Quality\QualityMetricCollection;
use App\Domain\Quality\QualityReport;
use App\Domain\Quality\QualityReportId;
use App\Domain\Quality\QualityScore;
use PHPUnit\Framework\TestCase;

final class QualityReportTest extends TestCase
{
    public function testCreateStoresReportFields(): void
    {
        $report = $this->createReport(94, PublicationRecommendation::Ready);

        self::assertTrue(QualityReportId::isValid($report->id()->value));
        self::assertSame(94, $report->overallScore()->value());
        self::assertSame(PublicationRecommendation::Ready, $report->recommendation());
        self::assertSame(5, $report->metrics()->count());
        self::assertNotEmpty($report->explanations());
    }

    public function testRecommendationForScoreThresholds(): void
    {
        self::assertSame(
            PublicationRecommendation::Ready,
            QualityReport::recommendationFor(QualityScore::create(94)),
        );
        self::assertSame(
            PublicationRecommendation::ReviewRecommended,
            QualityReport::recommendationFor(QualityScore::create(82)),
        );
        self::assertSame(
            PublicationRecommendation::RegenerateRequired,
            QualityReport::recommendationFor(QualityScore::create(60)),
        );
    }

    public function testMissingMetricThrows(): void
    {
        $this->expectException(InvalidQualityReportException::class);

        QualityReport::create(
            QualityReportId::generate(),
            new QualityMetricCollection([
                QualityMetric::create(QualityCategory::Audio, QualityScore::create(90)),
            ]),
            QualityScore::create(90),
            PublicationRecommendation::Ready,
        );
    }

    private function createReport(
        int $overall,
        PublicationRecommendation $recommendation,
    ): QualityReport {
        return QualityReport::create(
            QualityReportId::generate(),
            new QualityMetricCollection([
                QualityMetric::create(QualityCategory::Audio, QualityScore::create(98)),
                QualityMetric::create(QualityCategory::Translation, QualityScore::create(95)),
                QualityMetric::create(QualityCategory::VoiceClone, QualityScore::create(93)),
                QualityMetric::create(QualityCategory::LipSync, QualityScore::create(89)),
                QualityMetric::create(QualityCategory::Rendering, QualityScore::create(100)),
            ]),
            QualityScore::create($overall),
            $recommendation,
            ['High render quality preset applied.'],
        );
    }
}
