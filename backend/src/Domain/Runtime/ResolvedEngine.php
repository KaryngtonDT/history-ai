<?php

declare(strict_types=1);

namespace App\Domain\Runtime;

use App\Domain\Engine\EngineCatalogCapability;

final readonly class ResolvedEngine
{
    public function __construct(
        public string $engineId,
        public string $displayName,
        public EngineCatalogCapability $capability,
        public string $adapterKey,
        public RuntimeResolveReason $reason,
        public float $confidence,
        public bool $executable,
        public bool $blocked,
        public ?string $blockedReason = null,
        public ?string $provider = null,
        public ?string $executionProfile = null,
        public ?RuntimeFallbackPlan $fallback = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'engineId' => $this->engineId,
            'displayName' => $this->displayName,
            'capability' => $this->capability->value,
            'capabilityLabel' => $this->capability->label(),
            'adapterKey' => $this->adapterKey,
            'reason' => $this->reason->value,
            'reasonLabel' => $this->reason->label(),
            'confidence' => $this->confidence,
            'executable' => $this->executable,
            'blocked' => $this->blocked,
            'blockedReason' => $this->blockedReason,
            'provider' => $this->provider,
            'executionProfile' => $this->executionProfile,
            'fallback' => $this->fallback?->toArray(),
        ];
    }
}
