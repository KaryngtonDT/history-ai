<?php

declare(strict_types=1);

namespace App\Domain\Recommendation;

final readonly class ScoredRecommendationCollection
{
    /** @var list<ScoredRecommendation> */
    private array $recommendations;

    /**
     * @param list<ScoredRecommendation> $recommendations
     */
    public function __construct(array $recommendations)
    {
        $this->recommendations = array_values($recommendations);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<ScoredRecommendation>
     */
    public function recommendations(): array
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
}
