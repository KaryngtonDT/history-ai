<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Review;

use App\Domain\Review\Exception\InvalidReviewException;
use App\Domain\Review\ReviewComment;
use PHPUnit\Framework\TestCase;

final class ReviewCommentTest extends TestCase
{
    public function testTrimsComment(): void
    {
        self::assertSame('Robotic voice.', ReviewComment::fromString('  Robotic voice.  ')->value());
    }

    public function testEmptyCommentIsAllowed(): void
    {
        self::assertTrue(ReviewComment::empty()->isEmpty());
    }

    public function testRejectsOverlongComment(): void
    {
        $this->expectException(InvalidReviewException::class);

        ReviewComment::fromString(str_repeat('a', 2001));
    }
}
