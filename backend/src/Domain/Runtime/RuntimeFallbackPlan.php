<?php

declare(strict_types=1);

namespace App\Domain\Runtime;

final readonly class RuntimeFallbackPlan
{
    public function __construct(
        public string $engineId,
        public string $adapterKey,
        public RuntimeResolveReason $reason,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'engineId' => $this->engineId,
            'adapterKey' => $this->adapterKey,
            'reason' => $this->reason->value,
            'reasonLabel' => $this->reason->label(),
        ];
    }
}
