<?php

declare(strict_types=1);

namespace App\Domain\Shadow;

final readonly class ShadowInterventionCollection
{
    /**
     * @param list<ShadowIntervention> $interventions
     */
    public function __construct(private array $interventions)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function append(ShadowIntervention $intervention): self
    {
        return new self([...$this->interventions, $intervention]);
    }

    /**
     * @return list<ShadowIntervention>
     */
    public function all(): array
    {
        return $this->interventions;
    }

    public function count(): int
    {
        return count($this->interventions);
    }

    public function isEmpty(): bool
    {
        return [] === $this->interventions;
    }

    public function findById(ShadowInterventionId $id): ?ShadowIntervention
    {
        foreach ($this->interventions as $intervention) {
            if ($intervention->id()->equals($id)) {
                return $intervention;
            }
        }

        return null;
    }

    public function replace(ShadowIntervention $updated): self
    {
        $replaced = [];

        foreach ($this->interventions as $intervention) {
            $replaced[] = $intervention->id()->equals($updated->id()) ? $updated : $intervention;
        }

        return new self($replaced);
    }

    /**
     * @return list<ShadowIntervention>
     */
    public function recent(int $limit): array
    {
        if ($limit < 1) {
            return [];
        }

        return array_slice($this->interventions, -$limit);
    }
}
