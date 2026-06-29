<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Video;

use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;
use PHPUnit\Framework\TestCase;

final class VideoIdTest extends TestCase
{
    public function testAcceptsValidUuid(): void
    {
        $id = new VideoId('550e8400-e29b-41d4-a716-446655440000');

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $id->value);
    }

    public function testRejectsInvalidUuid(): void
    {
        $this->expectException(InvalidVideoIdException::class);

        new VideoId('not-a-uuid');
    }

    public function testGenerateCreatesValidUuid(): void
    {
        $id = VideoId::generate();

        self::assertTrue(VideoId::isValid($id->value));
    }

    public function testEqualsComparesValue(): void
    {
        $left = new VideoId('550e8400-e29b-41d4-a716-446655440000');
        $right = new VideoId('550e8400-e29b-41d4-a716-446655440000');
        $other = VideoId::generate();

        self::assertTrue($left->equals($right));
        self::assertFalse($left->equals($other));
    }
}
