<?php

declare(strict_types=1);

namespace App\Domain\Shadow;

final readonly class ShadowInteractionCollection
{
    /**
     * @param list<ShadowInteraction> $interactions
     */
    public function __construct(private array $interactions)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function append(ShadowInteraction $interaction): self
    {
        return new self([...$this->interactions, $interaction]);
    }

    /**
     * @return list<ShadowInteraction>
     */
    public function all(): array
    {
        return $this->interactions;
    }

    public function count(): int
    {
        return count($this->interactions);
    }

    public function isEmpty(): bool
    {
        return [] === $this->interactions;
    }

    /**
     * @return list<ShadowInteraction>
     */
    public function recent(int $limit): array
    {
        if ($limit < 1) {
            return [];
        }

        return array_slice($this->interactions, -$limit);
    }
}
