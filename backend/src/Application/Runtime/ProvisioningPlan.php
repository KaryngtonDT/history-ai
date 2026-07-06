<?php

declare(strict_types=1);

namespace App\Application\Runtime;

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
