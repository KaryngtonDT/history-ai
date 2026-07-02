<?php

declare(strict_types=1);

namespace App\Domain\ShadowMemory;

final readonly class MemoryEntryCollection
{
    /** @param list<MemoryEntry> $entries */
    public function __construct(private array $entries)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<MemoryEntry> */
    public function all(): array
    {
        return $this->entries;
    }

    public function append(MemoryEntry $entry): self
    {
        return new self([...$this->entries, $entry]);
    }

    /** @return list<MemoryEntry> */
    public function byCategory(MemoryCategory $category): array
    {
        return array_values(array_filter(
            $this->entries,
            static fn (MemoryEntry $entry): bool => $entry->category() === $category,
        ));
    }

    /** @return list<MemoryEntry> */
    public function recent(int $limit = 20): array
    {
        $sorted = $this->entries;
        usort(
            $sorted,
            static fn (MemoryEntry $a, MemoryEntry $b): int => $b->recordedAt() <=> $a->recordedAt(),
        );

        return array_slice($sorted, 0, $limit);
    }
}
