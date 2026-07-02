<?php

declare(strict_types=1);

namespace App\Domain\ShadowGoals;

final readonly class GoalConstraint
{
    public function __construct(
        private string $key,
        private string $label,
        private string $detail,
    ) {
    }

    public static function create(string $key, string $label, string $detail = ''): self
    {
        return new self($key, $label, $detail);
    }

    public function key(): string
    {
        return $this->key;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function detail(): string
    {
        return $this->detail;
    }
}
