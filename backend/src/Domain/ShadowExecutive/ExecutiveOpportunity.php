<?php

declare(strict_types=1);

namespace App\Domain\ShadowExecutive;

final readonly class ExecutiveOpportunity
{
    public function __construct(
        private string $id,
        private string $label,
        private string $detail,
        private string $source,
    ) {
    }

    public static function create(string $label, string $detail, string $source): self
    {
        return new self(
            bin2hex(random_bytes(8)),
            $label,
            $detail,
            $source,
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function detail(): string
    {
        return $this->detail;
    }

    public function source(): string
    {
        return $this->source;
    }
}
