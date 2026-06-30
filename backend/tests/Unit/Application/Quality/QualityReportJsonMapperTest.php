<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Quality;

use App\Application\Quality\QualityReportJsonMapper;
use App\Domain\Quality\Exception\InvalidQualityReportException;
use App\Domain\Quality\PublicationRecommendation;
use App\Domain\Quality\QualityCategory;
use App\Domain\Quality\QualityMetric;
use App\Domain\Quality\QualityMetricCollection;
use App\Domain\Quality\QualityReport;
use App\Domain\Quality\QualityReportId;
use App\Domain\Quality\QualityScore;
use PHPUnit\Framework\TestCase;

final class QualityReportJsonMapperTest extends TestCase
{
    private QualityReportJsonMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new QualityReportJsonMapper();
    }

    public function testRoundTripsQualityReport(): void
    {
        $report = QualityReport::create(
            QualityReportId::generate(),
            new QualityMetricCollection([
                QualityMetric::create(QualityCategory::Audio, QualityScore::create(98), 'Clean audio.'),
                QualityMetric::create(QualityCategory::Translation, QualityScore::create(95), 'Strong translation.'),
                QualityMetric::create(QualityCategory::VoiceClone, QualityScore::create(93), 'Natural voice.'),
                QualityMetric::create(QualityCategory::LipSync, QualityScore::create(89), 'Good lip sync.'),
                QualityMetric::create(QualityCategory::Rendering, QualityScore::create(100), 'High render quality.'),
            ]),
            QualityScore::create(94),
            PublicationRecommendation::Ready,
            ['Ready for publishing.'],
        );

        $decoded = $this->mapper->fromJson($this->mapper->toJson($report));

        self::assertSame($report->id()->value, $decoded->id()->value);
        self::assertSame(94, $decoded->overallScore()->value());
        self::assertSame(PublicationRecommendation::Ready, $decoded->recommendation());
        self::assertSame('Clean audio.', $decoded->metrics()->forCategory(QualityCategory::Audio)?->explanation());
    }

    public function testThrowsWhenJsonIsInvalid(): void
    {
        $this->expectException(InvalidQualityReportException::class);

        $this->mapper->fromJson('{invalid');
    }
}
