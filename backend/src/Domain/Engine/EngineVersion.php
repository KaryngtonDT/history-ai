<?php

declare(strict_types=1);

namespace App\Domain\Engine;

final readonly class EngineVersion
{
    public function __construct(
        public string $value,
        public ?string $build = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'build' => $this->build,
        ];
    }
}
