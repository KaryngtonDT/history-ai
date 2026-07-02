<?php

declare(strict_types=1);

namespace App\Domain\ShadowTeaching;

final readonly class LearningModule
{
    /** @param list<LearningObjective> $objectives */
    public function __construct(
        private string $key,
        private string $title,
        private array $objectives,
    ) {
    }

    public function key(): string
    {
        return $this->key;
    }

    public function title(): string
    {
        return $this->title;
    }

    /** @return list<LearningObjective> */
    public function objectives(): array
    {
        return $this->objectives;
    }
}
