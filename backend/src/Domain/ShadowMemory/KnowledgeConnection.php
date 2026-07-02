<?php

declare(strict_types=1);

namespace App\Domain\ShadowMemory;

final readonly class KnowledgeConnection
{
    public function __construct(
        private string $fromKey,
        private string $toKey,
        private string $label,
        private string $reason,
    ) {
    }

    public static function link(string $fromKey, string $toKey, string $label, string $reason): self
    {
        return new self($fromKey, $toKey, $label, $reason);
    }

    public function fromKey(): string
    {
        return $this->fromKey;
    }

    public function toKey(): string
    {
        return $this->toKey;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function reason(): string
    {
        return $this->reason;
    }
}
