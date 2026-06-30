<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Review;

use App\Domain\Review\Exception\InvalidReviewException;
use App\Domain\Review\ReviewScore;
use PHPUnit\Framework\TestCase;

final class ReviewScoreTest extends TestCase
{
    public function testAcceptsValidScores(): void
    {
        foreach ([1, 3, 5] as $value) {
            self::assertSame($value, ReviewScore::fromInt($value)->value());
        }
    }

    public function testRejectsOutOfRangeScore(): void
    {
        $this->expectException(InvalidReviewException::class);

        ReviewScore::fromInt(6);
    }

    public function testAverageRoundsToNearestInteger(): void
    {
        $average = ReviewScore::fromInt(4)->averageWith(ReviewScore::fromInt(5));

        self::assertSame(5, $average->value());
    }
}
