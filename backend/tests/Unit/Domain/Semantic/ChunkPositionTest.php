<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Semantic;

use App\Domain\Semantic\Exception\InvalidChunkException;
use App\Domain\Semantic\ChunkPosition;
use PHPUnit\Framework\TestCase;

final class ChunkPositionTest extends TestCase
{
    public function testAcceptsZeroPosition(): void
    {
        $position = new ChunkPosition(0);

        self::assertSame(0, $position->value());
    }

    public function testAcceptsPositivePosition(): void
    {
        $position = new ChunkPosition(3);

        self::assertSame(3, $position->value());
    }

    public function testRejectsNegativePosition(): void
    {
        $this->expectException(InvalidChunkException::class);

        new ChunkPosition(-1);
    }

    public function testEqualsComparesValue(): void
    {
        self::assertTrue((new ChunkPosition(2))->equals(new ChunkPosition(2)));
        self::assertFalse((new ChunkPosition(2))->equals(new ChunkPosition(1)));
    }
}
