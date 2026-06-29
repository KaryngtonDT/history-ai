<?php

declare(strict_types=1);

namespace App\Infrastructure\Semantic;

use App\Domain\Semantic\EmbeddingCacheInterface;
use App\Domain\Semantic\EmbeddingCacheKey;
use App\Domain\Semantic\EmbeddingVector;

/**
 * In-memory LRU embedding cache.
 * Evicts the least recently used entry when max size is exceeded.
 */
final class InMemoryEmbeddingCache implements EmbeddingCacheInterface
{
    /** @var array<string, EmbeddingVector> */
    private array $entries = [];

    /** @var list<string> */
    private array $accessOrder = [];

    public function __construct(
        private readonly int $maxSize = 1000,
    ) {
        if ($maxSize < 1) {
            throw new \InvalidArgumentException('Max size must be at least 1.');
        }
    }

    public function get(EmbeddingCacheKey $key): ?EmbeddingVector
    {
        if (!isset($this->entries[$key->value])) {
            return null;
        }

        $this->touch($key->value);

        return $this->entries[$key->value];
    }

    public function put(EmbeddingCacheKey $key, EmbeddingVector $vector): void
    {
        if (isset($this->entries[$key->value])) {
            $this->entries[$key->value] = $vector;
            $this->touch($key->value);

            return;
        }

        $this->entries[$key->value] = $vector;
        $this->accessOrder[] = $key->value;

        if (count($this->accessOrder) > $this->maxSize) {
            $evictedKey = array_shift($this->accessOrder);
            unset($this->entries[$evictedKey]);
        }
    }

    public function count(): int
    {
        return count($this->entries);
    }

    private function touch(string $key): void
    {
        $index = array_search($key, $this->accessOrder, true);

        if (false === $index) {
            $this->accessOrder[] = $key;

            return;
        }

        unset($this->accessOrder[$index]);
        $this->accessOrder = array_values($this->accessOrder);
        $this->accessOrder[] = $key;
    }
}
