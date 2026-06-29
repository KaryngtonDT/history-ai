<?php

declare(strict_types=1);

namespace App\Infrastructure\Semantic;

use App\Domain\Semantic\ChunkText;
use App\Domain\Semantic\EmbeddingCacheInterface;
use App\Domain\Semantic\EmbeddingCacheKey;
use App\Domain\Semantic\EmbeddingProviderInterface;
use App\Domain\Semantic\EmbeddingVector;

final class CachedEmbeddingProvider implements EmbeddingProviderInterface
{
    public function __construct(
        private readonly EmbeddingProviderInterface $inner,
        private readonly EmbeddingCacheInterface $cache,
    ) {
    }

    public function generateEmbedding(ChunkText $text): EmbeddingVector
    {
        $key = EmbeddingCacheKey::fromChunkText($text);
        $cached = $this->cache->get($key);

        if (null !== $cached) {
            return $cached;
        }

        $vector = $this->inner->generateEmbedding($text);
        $this->cache->put($key, $vector);

        return $vector;
    }
}
