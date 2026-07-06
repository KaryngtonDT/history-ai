<?php

declare(strict_types=1);

namespace App\Application\Runtime;

use App\Domain\Engine\Engine;
use App\Domain\Engine\EngineCatalogCapability;
use App\Domain\Hardware\HardwareCompatibilityResult;
use App\Domain\Hardware\HardwareDetectionReport;
use App\Domain\Hardware\HardwareProvider;
use App\Domain\Engine\EngineRepositoryInterface;
use App\Infrastructure\Runtime\Provisioning\EngineProvisioningCatalog;

final class IntelligentProvisioningPlanner
{
    /**
     * @var list<EngineCatalogCapability>
     */
    private const SINGLE_ENGINE_CAPABILITIES = [
        EngineCatalogCapability::LipSync,
    ];

    public function __construct(
        private readonly EngineRepositoryInterface $engineRepository,
        private readonly EngineCompatibilityEvaluator $compatibilityEvaluator,
    ) {
    }

    public function plan(HardwareDetectionReport $report): ProvisioningPlan
    {
        $toProvision = [];
        $skipped = [];
        $plannedIds = [];
        $plannedCapabilities = [];

        foreach ($this->engineRepository->all() as $engine) {
            $compatibility = $this->compatibilityEvaluator->evaluate(
                $engine,
                $report->profile->type,
                $report->capabilities,
                HardwareProvider::Host,
            );

            if ($engine->isReady()) {
                continue;
            }

            if (!$compatibility->hardwareCompatible) {
                $skipped[] = $this->skippedEntry($engine, $compatibility);
                $this->scheduleAlternative(
                    $compatibility,
                    $report,
                    $toProvision,
                    $plannedIds,
                    $plannedCapabilities,
                );
                continue;
            }

            $spec = EngineProvisioningCatalog::find($engine->id);
            if (null === $spec || !$spec->autoProvisionSupported) {
                continue;
            }

            if ($this->shouldSkipDuplicateCapability($engine, $plannedCapabilities)) {
                continue;
            }

            if (isset($plannedIds[$engine->id])) {
                continue;
            }

            $toProvision[] = new ProvisioningPlanEntry(
                engineId: $engine->id,
                capability: $engine->capability->value,
                reason: 'Hardware-compatible and auto-provision supported.',
            );
            $plannedIds[$engine->id] = true;
            $plannedCapabilities[$engine->capability->value][] = $engine->id;
        }

        return new ProvisioningPlan(
            hardwareProfile: $report->profile->type->value,
            toProvision: $toProvision,
            skipped: $skipped,
            sourceReportAt: $report->detectedAt->format(DATE_ATOM),
        );
    }

    /**
     * @param array<string, true> $plannedIds
     * @param array<string, list<string>> $plannedCapabilities
     * @param list<ProvisioningPlanEntry> $toProvision
     */
    private function scheduleAlternative(
        HardwareCompatibilityResult $compatibility,
        HardwareDetectionReport $report,
        array &$toProvision,
        array &$plannedIds,
        array &$plannedCapabilities,
    ): void {
        $alternativeId = $compatibility->recommendedAlternative;
        if (null === $alternativeId || isset($plannedIds[$alternativeId])) {
            return;
        }

        $alternative = $this->engineRepository->findById($alternativeId);
        if (null === $alternative || $alternative->isReady()) {
            return;
        }

        $altCompatibility = $this->compatibilityEvaluator->evaluate(
            $alternative,
            $report->profile->type,
            $report->capabilities,
            HardwareProvider::Host,
        );

        if (!$altCompatibility->hardwareCompatible) {
            return;
        }

        $spec = EngineProvisioningCatalog::find($alternativeId);
        if (null === $spec || !$spec->autoProvisionSupported) {
            return;
        }

        $capability = $alternative->capability->value;
        if ($this->capabilityAlreadyPlanned($alternative->capability, $plannedCapabilities)) {
            return;
        }

        $toProvision[] = new ProvisioningPlanEntry(
            engineId: $alternativeId,
            capability: $capability,
            reason: sprintf(
                'Compatible alternative for %s on %s hardware.',
                $compatibility->engineId,
                $report->profile->type->value,
            ),
            isAlternative: true,
            replacesEngineId: $compatibility->engineId,
        );
        $plannedIds[$alternativeId] = true;
        $plannedCapabilities[$capability][] = $alternativeId;
    }

    private function skippedEntry(Engine $engine, HardwareCompatibilityResult $compatibility): ProvisioningSkippedEntry
    {
        return new ProvisioningSkippedEntry(
            engineId: $engine->id,
            capability: $engine->capability->value,
            blockedReasonCode: $compatibility->blockedReasonCode->value,
            humanReason: $compatibility->humanReason,
            compatibleProviders: $this->compatibleProviders($compatibility),
            recommendedAlternative: $compatibility->recommendedAlternative,
            installAttempted: false,
        );
    }

    /**
     * @return list<string>
     */
    private function compatibleProviders(HardwareCompatibilityResult $compatibility): array
    {
        $providers = [];
        if ($compatibility->canBeFixedByRemoteProvider) {
            $providers[] = HardwareProvider::Remote->value;
        }
        if ($compatibility->canBeFixedByHardware) {
            $providers[] = HardwareProvider::Host->value;
        }

        return $providers;
    }

    /**
     * @param array<string, list<string>> $plannedCapabilities
     */
    private function shouldSkipDuplicateCapability(Engine $engine, array $plannedCapabilities): bool
    {
        if (!in_array($engine->capability, self::SINGLE_ENGINE_CAPABILITIES, true)) {
            return false;
        }

        return $this->capabilityAlreadyPlanned($engine->capability, $plannedCapabilities);
    }

    /**
     * @param array<string, list<string>> $plannedCapabilities
     */
    private function capabilityAlreadyPlanned(EngineCatalogCapability $capability, array $plannedCapabilities): bool
    {
        if (!in_array($capability, self::SINGLE_ENGINE_CAPABILITIES, true)) {
            return false;
        }

        return isset($plannedCapabilities[$capability->value])
            && [] !== $plannedCapabilities[$capability->value];
    }
}
