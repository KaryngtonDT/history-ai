<?php

declare(strict_types=1);

namespace App\Domain\ShadowSecondBrain;

final readonly class KnowledgeDomainHeatmapEntry
{
    public function __construct(
        private string $key,
        private string $label,
        private int $percent,
    ) {
    }

    public function key(): string
    {
        return $this->key;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function percent(): int
    {
        return $this->percent;
    }
}
