<?php

declare(strict_types=1);

namespace App\Domain\ShadowMentor;

final readonly class SkillProgress
{
    public function __construct(
        private string $key,
        private string $label,
        private int $percent,
    ) {
    }

    public static function create(string $key, string $label, int $percent): self
    {
        return new self($key, $label, max(0, min(100, $percent)));
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
