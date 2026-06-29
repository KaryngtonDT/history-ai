<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Semantic;

use App\Domain\Semantic\ChunkText;
use App\Domain\Semantic\EmbeddingCacheKey;
use App\Domain\Semantic\EmbeddingProviderInterface;
use App\Domain\Semantic\EmbeddingVector;
use App\Infrastructure\Semantic\CachedEmbeddingProvider;
use App\Infrastructure\Semantic\InMemoryEmbeddingCache;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CachedEmbeddingProviderTest extends TestCase
{
    private InMemoryEmbeddingCache $cache;

    private EmbeddingProviderInterface&MockObject $inner;

    protected function setUp(): void
    {
        $this->cache = new InMemoryEmbeddingCache();
        $this->inner = $this->createMock(EmbeddingProviderInterface::class);
    }

    public function testCacheHitAvoidsInnerProviderCall(): void
    {
        $text = ChunkText::fromString('Cached provider text');
        $vector = new EmbeddingVector([0.1, 0.2, 0.3]);

        $this->inner
            ->expects(self::once())
            ->method('generateEmbedding')
            ->with($text)
            ->willReturn($vector);

        $provider = $this->createProvider();

        self::assertTrue($provider->generateEmbedding($text)->equals($vector));
        self::assertTrue($provider->generateEmbedding($text)->equals($vector));
    }

    public function testCacheMissStoresResultFromInnerProvider(): void
    {
        $text = ChunkText::fromString('Miss then hit');
        $vector = new EmbeddingVector([0.4, 0.5]);

        $this->inner
            ->expects(self::once())
            ->method('generateEmbedding')
            ->willReturn($vector);

        $provider = $this->createProvider();
        $result = $provider->generateEmbedding($text);

        self::assertTrue($vector->equals($result));
        self::assertTrue($vector->equals($this->cache->get(
            EmbeddingCacheKey::fromChunkText($text),
        )));
    }

    private function createProvider(): CachedEmbeddingProvider
    {
        return new CachedEmbeddingProvider(
            $this->inner,
            $this->cache,
        );
    }
}
