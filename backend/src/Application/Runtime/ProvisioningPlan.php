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

final readonly class ProvisioningSkippedEntry
{
    /**
     * @param list<string> $compatibleProviders
     */
    public function __construct(
        public string $engineId,
        public string $capability,
        public string $blockedReasonCode,
        public string $humanReason,
        public array $compatibleProviders,
        public ?string $recommendedAlternative,
        public bool $installAttempted = false,
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
            'status' => 'blocked',
            'blockedReasonCode' => $this->blockedReasonCode,
            'humanReason' => $this->humanReason,
            'compatibleProviders' => $this->compatibleProviders,
            'recommendedAlternative' => $this->recommendedAlternative,
            'installAttempted' => $this->installAttempted,
        ];
    }
}

final readonly class ProvisioningPlan
{
    /**
     * @param list<ProvisioningPlanEntry> $toProvision
     * @param list<ProvisioningSkippedEntry> $skipped
     */
    public function __construct(
        public string $hardwareProfile,
        public array $toProvision,
        public array $skipped,
        public string $sourceReportAt,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'hardwareProfile' => $this->hardwareProfile,
            'sourceReportAt' => $this->sourceReportAt,
            'provisionCount' => count($this->toProvision),
            'skippedCount' => count($this->skipped),
            'toProvision' => array_map(static fn (ProvisioningPlanEntry $e): array => $e->toArray(), $this->toProvision),
            'skipped' => array_map(static fn (ProvisioningSkippedEntry $e): array => $e->toArray(), $this->skipped),
        ];
    }
}
