<?php

declare(strict_types=1);

namespace App\Domain\Shadow\SessionLearning;

final readonly class StrategyAdjustmentCollection
{
    /**
     * @param list<StrategyAdjustment> $items
     */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<StrategyAdjustment>
     */
    public function all(): array
    {
        return $this->items;
    }

    public function append(StrategyAdjustment $adjustment): self
    {
        return new self([...$this->items, $adjustment]);
    }
}
