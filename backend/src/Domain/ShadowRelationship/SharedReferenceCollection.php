<?php

declare(strict_types=1);

namespace App\Domain\ShadowRelationship;

final readonly class SharedReferenceCollection
{
    /** @param list<SharedReference> $references */
    public function __construct(private array $references)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<SharedReference> */
    public function all(): array
    {
        return $this->references;
    }

    public function append(SharedReference $reference): self
    {
        return new self([...$this->references, $reference]);
    }

    /** @return list<SharedReference> */
    public function recent(int $limit = 5): array
    {
        $sorted = $this->references;
        usort(
            $sorted,
            static fn (SharedReference $a, SharedReference $b): int => $b->recordedAt() <=> $a->recordedAt(),
        );

        return array_slice($sorted, 0, $limit);
    }
}
