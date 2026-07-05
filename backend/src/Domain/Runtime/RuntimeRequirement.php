<?php

declare(strict_types=1);

namespace App\Domain\Runtime;

final readonly class RuntimeRequirement
{
    public function __construct(
        public string $key,
        public string $label,
        public bool $satisfied,
        public ?string $detail = null,
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
        ];
    }
}
