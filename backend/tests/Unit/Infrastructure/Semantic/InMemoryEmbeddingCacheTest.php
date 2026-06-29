<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Semantic;

use App\Domain\Semantic\ChunkText;
use App\Domain\Semantic\EmbeddingCacheKey;
use App\Domain\Semantic\EmbeddingVector;
use App\Infrastructure\Semantic\InMemoryEmbeddingCache;
use PHPUnit\Framework\TestCase;

final class InMemoryEmbeddingCacheTest extends TestCase
{
    public function testStoresAndReturnsVectors(): void
    {
        $cache = new InMemoryEmbeddingCache();
        $key = EmbeddingCacheKey::fromChunkText(ChunkText::fromString('Cached chunk'));
        $vector = new EmbeddingVector([0.1, 0.2, 0.3]);

        $cache->put($key, $vector);

        self::assertTrue($vector->equals($cache->get($key)));
    }

    public function testReturnsNullOnMiss(): void
    {
        $cache = new InMemoryEmbeddingCache();
        $key = EmbeddingCacheKey::fromChunkText(ChunkText::fromString('Missing chunk'));

        self::assertNull($cache->get($key));
    }

    public function testEvictsLeastRecentlyUsedEntryWhenMaxSizeExceeded(): void
    {
        $cache = new InMemoryEmbeddingCache(maxSize: 2);
        $firstKey = EmbeddingCacheKey::fromChunkText(ChunkText::fromString('First'));
        $secondKey = EmbeddingCacheKey::fromChunkText(ChunkText::fromString('Second'));
        $thirdKey = EmbeddingCacheKey::fromChunkText(ChunkText::fromString('Third'));

        $cache->put($firstKey, new EmbeddingVector([0.1]));
        $cache->put($secondKey, new EmbeddingVector([0.2]));
        $cache->get($secondKey);
        $cache->put($thirdKey, new EmbeddingVector([0.3]));

        self::assertSame(2, $cache->count());
        self::assertNull($cache->get($firstKey));
        self::assertNotNull($cache->get($secondKey));
        self::assertNotNull($cache->get($thirdKey));
    }

    public function testUpdatesExistingEntryWithoutIncreasingSize(): void
    {
        $cache = new InMemoryEmbeddingCache(maxSize: 2);
        $key = EmbeddingCacheKey::fromChunkText(ChunkText::fromString('Updated chunk'));
        $firstVector = new EmbeddingVector([0.1]);
        $secondVector = new EmbeddingVector([0.9]);

        $cache->put($key, $firstVector);
        $cache->put($key, $secondVector);

        self::assertSame(1, $cache->count());
        self::assertTrue($secondVector->equals($cache->get($key)));
    }
}
