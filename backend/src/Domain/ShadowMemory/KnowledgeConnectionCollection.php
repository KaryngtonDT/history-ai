<?php

declare(strict_types=1);

namespace App\Domain\ShadowMemory;

final readonly class KnowledgeConnectionCollection
{
    /** @param list<KnowledgeConnection> $connections */
    public function __construct(private array $connections)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<KnowledgeConnection> */
    public function all(): array
    {
        return $this->connections;
    }

    public function append(KnowledgeConnection $connection): self
    {
        foreach ($this->connections as $existing) {
            if ($existing->fromKey() === $connection->fromKey() && $existing->toKey() === $connection->toKey()) {
                return $this;
            }
        }

        return new self([...$this->connections, $connection]);
    }

    /** @return list<KnowledgeConnection> */
    public function forKey(string $key): array
    {
        return array_values(array_filter(
            $this->connections,
            static fn (KnowledgeConnection $connection): bool => $connection->fromKey() === $key || $connection->toKey() === $key,
        ));
    }
}
