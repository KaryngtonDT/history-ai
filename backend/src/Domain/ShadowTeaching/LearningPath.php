<?php

declare(strict_types=1);

namespace App\Domain\ShadowTeaching;

final readonly class LearningPath
{
    /** @param list<LearningModule> $modules */
    public function __construct(
        private string $title,
        private string $goal,
        private array $modules,
    ) {
    }

    public static function empty(): self
    {
        return new self('Personal learning path', 'Grow with Shadow', []);
    }

    public function title(): string
    {
        return $this->title;
    }

    public function goal(): string
    {
        return $this->goal;
    }

    /** @return list<LearningModule> */
    public function modules(): array
    {
        return $this->modules;
    }
}
