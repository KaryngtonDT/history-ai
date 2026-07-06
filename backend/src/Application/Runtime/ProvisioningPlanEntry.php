<?php

declare(strict_types=1);

namespace App\Application\Runtime;

final readonly class ProvisioningPlanEntry
{
    public function __construct(
        public string $engineId,
        public string $capability,
        public string $reason,
        public bool $isAlternative = false,
        public ?string $replacesEngineId = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'engineId' => $this->engineId,
            'capability' => $this->capability,
            'reason' => $this->reason,
            'isAlternative' => $this->isAlternative,
            'replacesEngineId' => $this->replacesEngineId,
        ];
    }
}
