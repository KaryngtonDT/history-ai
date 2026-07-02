<?php

declare(strict_types=1);

namespace App\Domain\ShadowIdentity;

final readonly class ShadowIdentitySnapshotCollection
{
    /**
     * @param list<ShadowIdentitySnapshot> $snapshots
     */
    public function __construct(private array $snapshots)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function append(ShadowIdentitySnapshot $snapshot): self
    {
        return new self([...$this->snapshots, $snapshot]);
    }

    /**
     * @return list<ShadowIdentitySnapshot>
     */
    public function all(): array
    {
        return $this->snapshots;
    }

    public function count(): int
    {
        return count($this->snapshots);
    }

    public function latest(): ?ShadowIdentitySnapshot
    {
        if ([] === $this->snapshots) {
            return null;
        }

        return $this->snapshots[array_key_last($this->snapshots)];
    }

    public function recent(int $limit): self
    {
        if ($limit < 1) {
            return self::empty();
        }

        return new self(array_slice($this->snapshots, -$limit));
    }
}
