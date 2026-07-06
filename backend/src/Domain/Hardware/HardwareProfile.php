<?php

declare(strict_types=1);

namespace App\Domain\Hardware;

final readonly class HardwareProfile
{
    public function __construct(
        public HardwareProfileType $type,
        public HardwareCapability $capabilities,
        public string $summary,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'label' => $this->type->label(),
            'summary' => $this->summary,
            'capabilities' => $this->capabilities->toArray(),
        ];
    }
}
