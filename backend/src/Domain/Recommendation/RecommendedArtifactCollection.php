<?php

declare(strict_types=1);

namespace App\Domain\Recommendation;

final readonly class RecommendedArtifactCollection
{
    /** @var list<RecommendedArtifact> */
    private array $recommendations;

    /**
     * @param list<RecommendedArtifact> $recommendations
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
     * @return list<RecommendedArtifact>
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
