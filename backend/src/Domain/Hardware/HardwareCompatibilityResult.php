<?php

declare(strict_types=1);

namespace App\Domain\Hardware;

final readonly class HardwareCompatibilityResult
{
    /**
     * @param list<string> $missingRequirements
     * @param list<FixType> $fixTypes
     */
    public function __construct(
        public string $engineId,
        public string $status,
        public HardwareProfileType $hardwareProfile,
        public BlockedReasonCode $blockedReasonCode,
        public string $humanReason,
        public array $missingRequirements,
        public ?string $recommendedAlternative,
        public bool $canBeFixedByInstall,
        public bool $canBeFixedByHardware,
        public bool $canBeFixedByRemoteProvider,
        public CompatibilitySeverity $severity,
        public HardwareProvider $provider,
        public array $fixTypes = [],
        public ?string $documentationLink = null,
        public bool $hardwareCompatible = true,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'engineId' => $this->engineId,
            'status' => $this->status,
            'hardwareProfile' => $this->hardwareProfile->value,
            'hardwareProfileLabel' => $this->hardwareProfile->label(),
            'blockedReasonCode' => $this->blockedReasonCode->value,
            'blockedReasonLabel' => $this->blockedReasonCode->label(),
            'humanReason' => $this->humanReason,
            'missingRequirements' => $this->missingRequirements,
            'recommendedAlternative' => $this->recommendedAlternative,
            'canBeFixedByInstall' => $this->canBeFixedByInstall,
            'canBeFixedByHardware' => $this->canBeFixedByHardware,
            'canBeFixedByRemoteProvider' => $this->canBeFixedByRemoteProvider,
            'severity' => $this->severity->value,
            'provider' => $this->provider->value,
            'providerLabel' => $this->provider->label(),
            'fixTypes' => array_map(static fn (FixType $type): string => $type->value, $this->fixTypes),
            'fixTypeLabels' => array_map(static fn (FixType $type): string => $type->label(), $this->fixTypes),
            'documentationLink' => $this->documentationLink,
            'hardwareCompatible' => $this->hardwareCompatible,
        ];
    }
}
