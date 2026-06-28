<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Recommendation;

use App\Domain\Recommendation\Exception\InvalidRecommendationScoreException;
use App\Domain\Recommendation\RecommendationScore;
use PHPUnit\Framework\TestCase;

final class RecommendationScoreTest extends TestCase
{
    public function testAcceptsMinimumScore(): void
    {
        $score = new RecommendationScore(0);

        self::assertSame(0, $score->value());
    }

    public function testAcceptsMaximumScore(): void
    {
        $score = new RecommendationScore(100);

        self::assertSame(100, $score->value());
    }

    public function testRejectsNegativeScore(): void
    {
        $this->expectException(InvalidRecommendationScoreException::class);

        new RecommendationScore(-1);
    }

    public function testRejectsScoreAboveMaximum(): void
    {
        $this->expectException(InvalidRecommendationScoreException::class);

        new RecommendationScore(101);
    }

    public function testEqualsComparesValue(): void
    {
        self::assertTrue((new RecommendationScore(80))->equals(new RecommendationScore(80)));
        self::assertFalse((new RecommendationScore(80))->equals(new RecommendationScore(60)));
    }
}
