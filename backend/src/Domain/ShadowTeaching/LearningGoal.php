<?php

declare(strict_types=1);

namespace App\Domain\ShadowTeaching;

final readonly class LearningGoal
{
    public function __construct(
        private string $label,
        private string $description,
    ) {
    }

    public function label(): string
    {
        return $this->label;
    }

    public function description(): string
    {
        return $this->description;
    }
}
