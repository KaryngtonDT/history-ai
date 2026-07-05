<?php

declare(strict_types=1);

namespace App\Domain\Engine;

final readonly class EngineRequirement
{
    public function __construct(
        public string $key,
        public string $label,
        public bool $satisfied = false,
        public ?string $detail = null,
        public bool $optional = false,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'satisfied' => $this->satisfied,
            'detail' => $this->detail,
            'optional' => $this->optional,
        ];
    }
}
