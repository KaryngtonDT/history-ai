<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Quality;

use App\Domain\Quality\Exception\InvalidQualityReportException;
use App\Domain\Quality\QualityCategory;
use App\Domain\Quality\QualityMetric;
use App\Domain\Quality\QualityMetricCollection;
use App\Domain\Quality\QualityScore;
use PHPUnit\Framework\TestCase;

final class QualityMetricTest extends TestCase
{
    public function testCreateStoresCategoryScoreAndExplanation(): void
    {
        $metric = QualityMetric::create(
            QualityCategory::LipSync,
            QualityScore::create(89),
            'Lip visibility reduced lip-sync confidence.',
        );

        self::assertSame(QualityCategory::LipSync, $metric->category());
        self::assertSame(89, $metric->score()->value());
        self::assertSame('Lip visibility reduced lip-sync confidence.', $metric->explanation());
    }

    public function testOverallCategoryIsNotAllowedInMetric(): void
    {
        $this->expectException(InvalidQualityReportException::class);

        QualityMetric::create(QualityCategory::Overall, QualityScore::create(90));
    }

    public function testCollectionCalculatesAverage(): void
    {
        $collection = new QualityMetricCollection([
            QualityMetric::create(QualityCategory::Audio, QualityScore::create(98)),
            QualityMetric::create(QualityCategory::Translation, QualityScore::create(92)),
        ]);

        self::assertSame(95, $collection->averageScore()->value());
    }

    public function testDuplicateCategoriesThrow(): void
    {
        $this->expectException(InvalidQualityReportException::class);

        new QualityMetricCollection([
            QualityMetric::create(QualityCategory::Audio, QualityScore::create(90)),
            QualityMetric::create(QualityCategory::Audio, QualityScore::create(80)),
        ]);
    }
}
