<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Semantic;

use App\Domain\Semantic\ChunkText;
use App\Domain\Semantic\EmbeddingCacheKey;
use PHPUnit\Framework\TestCase;

final class EmbeddingCacheKeyTest extends TestCase
{
    public function testSameTextProducesSameKey(): void
    {
        $left = EmbeddingCacheKey::fromChunkText(ChunkText::fromString('Ancient Rome overview'));
        $right = EmbeddingCacheKey::fromChunkText(ChunkText::fromString('Ancient Rome overview'));

        self::assertTrue($left->equals($right));
        self::assertSame($left->value, $right->value);
    }

    public function testDifferentTextProducesDifferentKey(): void
    {
        $left = EmbeddingCacheKey::fromChunkText(ChunkText::fromString('Ancient Rome overview'));
        $right = EmbeddingCacheKey::fromChunkText(ChunkText::fromString('Greek history overview'));

        self::assertFalse($left->equals($right));
    }

    public function testKeyIsSha256Hash(): void
    {
        $text = ChunkText::fromString('Deterministic cache key');
        $key = EmbeddingCacheKey::fromChunkText($text);

        self::assertSame(hash('sha256', $text->value()), $key->value);
    }
}
