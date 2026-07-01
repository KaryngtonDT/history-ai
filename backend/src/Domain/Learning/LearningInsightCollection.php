<?php

declare(strict_types=1);

namespace App\Domain\Learning;

final readonly class LearningInsightCollection
{
    /**
     * @param list<LearningInsight> $insights
     */
    public function __construct(private array $insights)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function append(LearningInsight $insight): self
    {
        return new self([...$this->insights, $insight]);
    }

    /**
     * @return list<LearningInsight>
     */
    public function all(): array
    {
        return $this->insights;
    }

    public function count(): int
    {
        return count($this->insights);
    }

    public function isEmpty(): bool
    {
        return [] === $this->insights;
    }

    public function ofType(LearningInsightType $type): self
    {
        return new self(array_values(array_filter(
            $this->insights,
            static fn (LearningInsight $insight): bool => $insight->type() === $type,
        )));
    }
}
