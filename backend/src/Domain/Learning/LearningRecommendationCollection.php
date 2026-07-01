<?php

declare(strict_types=1);

namespace App\Domain\Learning;

final readonly class LearningRecommendationCollection
{
    /**
     * @param list<LearningRecommendation> $recommendations
     */
    public function __construct(private array $recommendations)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function append(LearningRecommendation $recommendation): self
    {
        return new self([...$this->recommendations, $recommendation]);
    }

    /**
     * @return list<LearningRecommendation>
     */
    public function all(): array
    {
        return $this->recommendations;
    }

    public function count(): int
    {
        return count($this->recommendations);
    }

    public function isEmpty(): bool
    {
        return [] === $this->recommendations;
    }

    public function ofType(LearningRecommendationType $type): self
    {
        return new self(array_values(array_filter(
            $this->recommendations,
            static fn (LearningRecommendation $recommendation): bool => $recommendation->type() === $type,
        )));
    }
}
