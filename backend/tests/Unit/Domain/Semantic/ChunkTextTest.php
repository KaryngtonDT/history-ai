<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Semantic;

use App\Domain\Semantic\Exception\InvalidChunkException;
use App\Domain\Semantic\ChunkText;
use PHPUnit\Framework\TestCase;

final class ChunkTextTest extends TestCase
{
    public function testTrimsWhitespace(): void
    {
        $text = ChunkText::fromString("  Roman Republic  \n");

        self::assertSame('Roman Republic', $text->value());
    }

    public function testRejectsEmptyString(): void
    {
        $this->expectException(InvalidChunkException::class);

        ChunkText::fromString('');
    }

    public function testRejectsWhitespaceOnlyString(): void
    {
        $this->expectException(InvalidChunkException::class);

        ChunkText::fromString("   \n\n  ");
    }

    public function testEqualsComparesValue(): void
    {
        self::assertTrue(
            ChunkText::fromString('Summary')->equals(ChunkText::fromString('Summary')),
        );
        self::assertFalse(
            ChunkText::fromString('Summary')->equals(ChunkText::fromString('Quiz')),
        );
    }
}
