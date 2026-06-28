<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Semantic;

use App\Domain\Semantic\Exception\InvalidSimilarityScoreException;
use App\Domain\Semantic\SimilarityScore;
use PHPUnit\Framework\TestCase;

final class SimilarityScoreTest extends TestCase
{
    public function testAcceptsMinimumScore(): void
    {
        $score = new SimilarityScore(0.0);

        self::assertSame(0.0, $score->value());
    }

    public function testAcceptsMaximumScore(): void
    {
        $score = new SimilarityScore(1.0);

        self::assertSame(1.0, $score->value());
    }

    public function testRejectsScoreBelowMinimum(): void
    {
        $this->expectException(InvalidSimilarityScoreException::class);

        new SimilarityScore(-0.01);
    }

    public function testRejectsScoreAboveMaximum(): void
    {
        $this->expectException(InvalidSimilarityScoreException::class);

        new SimilarityScore(1.01);
    }

    public function testEqualsComparesValue(): void
    {
        self::assertTrue((new SimilarityScore(0.8))->equals(new SimilarityScore(0.8)));
        self::assertFalse((new SimilarityScore(0.8))->equals(new SimilarityScore(0.6)));
    }
}
