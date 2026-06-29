<?php

declare(strict_types=1);

namespace App\Domain\Semantic;

interface EmbeddingCacheInterface
{
    public function get(EmbeddingCacheKey $key): ?EmbeddingVector;

    public function put(EmbeddingCacheKey $key, EmbeddingVector $vector): void;
}
