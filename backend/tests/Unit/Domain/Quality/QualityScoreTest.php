<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Quality;

use App\Domain\Quality\Exception\InvalidQualityReportException;
use App\Domain\Quality\QualityScore;
use PHPUnit\Framework\TestCase;

final class QualityScoreTest extends TestCase
{
    public function testCreateStoresValue(): void
    {
        $score = QualityScore::create(94);

        self::assertSame(94, $score->value());
    }

    public function testInvalidScoreThrows(): void
    {
        $this->expectException(InvalidQualityReportException::class);

        QualityScore::create(101);
    }

    public function testPenalizeAndBonusClampToRange(): void
    {
        $score = QualityScore::create(95);

        self::assertSame(85, $score->penalize(10)->value());
        self::assertSame(100, $score->bonus(10)->value());
        self::assertSame(80, QualityScore::create(90)->cap(80)->value());
    }
}
