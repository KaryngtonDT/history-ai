<?php

declare(strict_types=1);

namespace App\Domain\ShadowExecutive;

final readonly class ExecutiveReason
{
    public function __construct(
        private string $summary,
        private string $detail,
    ) {
    }

    public static function create(string $summary, string $detail = ''): self
    {
        return new self($summary, $detail);
    }

    public function summary(): string
    {
        return $this->summary;
    }

    public function detail(): string
    {
        return $this->detail;
    }
}
