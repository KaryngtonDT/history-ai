<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Health;

use App\Application\Runtime\RuntimeResolverInterface;
use App\Domain\Engine\EngineCatalogCapability;
use App\Domain\Runtime\RuntimeCapabilityClassification;
use App\Domain\Runtime\RuntimeEngine;
use App\Infrastructure\Runtime\Catalog\EngineCatalogDefinitions;
use App\Infrastructure\Runtime\Catalog\EngineRequirementMatrix;
use App\Infrastructure\Runtime\Catalog\RuntimeCapabilityClassificationRegistry;
use App\Infrastructure\Runtime\Compatibility\RuntimeCompatibilityService;
use App\Infrastructure\Runtime\Readiness\ReadinessEngine;

final class RuntimePlatformHealthService
{
    public function __construct(
        private readonly ReadinessEngine $readinessEngine,
        private readonly RuntimeResolverInterface $runtimeResolver,
        private readonly RuntimeCompatibilityService $compatibilityService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function evaluate(): array
    {
        $readiness = $this->readinessEngine->evaluate();
        $enginesById = [];
        foreach ($readiness->engines as $engine) {
            $enginesById[$engine->id] = $engine;
        }

        $compatById = [];
        foreach ($this->compatibilityService->evaluateAll() as $result) {
            $compatById[$result->engineId] = $result->toArray();
        }

        $capabilityStates = [];
        foreach (EngineCatalogCapability::cases() as $capability) {
            $capabilityStates[] = $this->evaluateCapability(
                $capability,
                $enginesById,
                $compatById,
            );
        }

        $grouped = $this->groupByClassification($capabilityStates);
        $coreReady = $this->countReady($grouped['core']);
        $coreTotal = count($grouped['core']);
        $corePercent = $coreTotal > 0 ? round(($coreReady / $coreTotal) * 100, 1) : 0.0;
        $coreHealthy = $coreReady === $coreTotal && $coreTotal > 0;

        $optionalInstalled = $this->countInstalled($grouped['optional']);
        $optionalTotal = count($grouped['optional']);
        $premiumAvailable = $this->countReady($grouped['premium']);
        $premiumTotal = count($grouped['premium']);
        $experimentalReady = $this->countReady($grouped['experimental']);
        $experimentalTotal = count($grouped['experimental']);
        $deprecatedCount = count($grouped['deprecated']);

        return [
            'coreHealth' => [
                'status' => $coreHealthy ? 'ready' : 'fail',
                'percent' => $corePercent,
                'readyCount' => $coreReady,
                'totalCount' => $coreTotal,
                'label' => 'Runtime Core',
            ],
            'extensionCoverage' => $this->buildCoverageSection(
                'Extensions',
                $grouped['optional'],
                $optionalInstalled,
                $optionalTotal,
            ),
            'premiumAvailability' => $this->buildPremiumSection(
                $grouped['premium'],
                $premiumAvailable,
                $premiumTotal,
            ),
            'experimentalCoverage' => [
                'label' => 'Experimental',
                'readyCount' => $experimentalReady,
                'totalCount' => $experimentalTotal,
                'disabledCount' => $this->countByAvailability($grouped['experimental'], 'disabled'),
                'capabilities' => $grouped['experimental'],
            ],
            'deprecatedCount' => $deprecatedCount,
            'capabilities' => $capabilityStates,
            'counters' => [
                'core' => ['ready' => $coreReady, 'total' => $coreTotal],
                'optional' => ['installed' => $optionalInstalled, 'total' => $optionalTotal],
                'premium' => ['available' => $premiumAvailable, 'total' => $premiumTotal],
                'experimental' => ['ready' => $experimentalReady, 'disabled' => $this->countByAvailability($grouped['experimental'], 'disabled'), 'total' => $experimentalTotal],
                'deprecated' => ['count' => $deprecatedCount],
            ],
        ];
    }

    /**
     * @param array<string, RuntimeEngine> $enginesById
     * @param array<string, array<string, mixed>> $compatById
     *
     * @return array<string, mixed>
     */
    private function evaluateCapability(
        EngineCatalogCapability $capability,
        array $enginesById,
        array $compatById,
    ): array {
        $meta = RuntimeCapabilityClassificationRegistry::for($capability);
        $view = $this->runtimeResolver->capabilitySelectionView($capability);
        $currentId = $view['currentEngineId'] ?? null;
        $recommendedId = $view['recommendedEngineId'] ?? null;
        $default = EngineCatalogDefinitions::defaultForCapability($capability);

        $currentEngine = is_string($currentId) ? ($enginesById[$currentId] ?? null) : null;
        $compat = is_string($currentId) ? ($compatById[$currentId] ?? null) : null;
        $hardwareCompatible = (bool) ($compat['hardwareCompatible'] ?? true);

        $availability = $this->resolveAvailability(
            $meta->classification,
            $currentEngine,
            $compat,
            $hardwareCompatible,
        );

        $reason = $this->resolveReason($availability, $currentEngine, $compat, $meta->classification);
        $futureHardware = $this->futureHardwareHint($currentId, $compat, $meta->classification);

        return [
            ...$meta->toArray(),
            'currentEngineId' => $currentId,
            'recommendedEngineId' => $recommendedId,
            'referenceEngineId' => $default?->id,
            'availability' => $availability,
            'availabilityLabel' => strtoupper(str_replace('_', ' ', $availability)),
            'ready' => 'ready' === $availability,
            'reason' => $reason,
            'hardwareCompatible' => $hardwareCompatible,
            'futureHardware' => $futureHardware,
        ];
    }

    /**
     * @param array<string, mixed>|null $compat
     */
    private function resolveAvailability(
        RuntimeCapabilityClassification $classification,
        ?RuntimeEngine $currentEngine,
        ?array $compat,
        bool $hardwareCompatible,
    ): string {
        if (null === $currentEngine) {
            return RuntimeCapabilityClassification::Optional === $classification
                ? 'not_installed'
                : 'not_installed';
        }

        if ($currentEngine->isReady()) {
            return 'ready';
        }

        if (!$hardwareCompatible || ($compat['blockedReasonCode'] ?? '') === 'nvidia_cuda_required') {
            return RuntimeCapabilityClassification::Premium === $classification
                ? 'unsupported_hardware'
                : 'blocked';
        }

        if (!$currentEngine->discovered && !$currentEngine->configured) {
            return 'not_installed';
        }

        if ('mock' === $currentEngine->mode->value) {
            return 'disabled';
        }

        return 'blocked';
    }

    /**
     * @param array<string, mixed>|null $compat
     *
     * @return array<string, mixed>|null
     */
    private function futureHardwareHint(
        ?string $engineId,
        ?array $compat,
        RuntimeCapabilityClassification $classification,
    ): ?array {
        if (RuntimeCapabilityClassification::Premium !== $classification || null === $engineId) {
            return null;
        }

        if (($compat['hardwareCompatible'] ?? true) && 'ready' === ($compat['status'] ?? '')) {
            return null;
        }

        $requirement = EngineRequirementMatrix::findByEngineId($engineId);
        $recommendedHardware = null;
        if (null !== $requirement?->requiredGpuVendor) {
            $recommendedHardware = match ($requirement->requiredGpuVendor) {
                'nvidia' => 'NVIDIA GPU with CUDA',
                'amd' => 'AMD GPU with ROCm',
                default => strtoupper($requirement->requiredGpuVendor) . ' GPU',
            };
            if (null !== $requirement->minimumVramGb && $requirement->minimumVramGb >= 12) {
                $recommendedHardware = 'RTX 4090 or equivalent (' . (int) $requirement->minimumVramGb . ' GB VRAM)';
            }
        }

        return [
            'recommendedHardware' => $recommendedHardware,
            'estimatedPremiumScoreGain' => null !== $recommendedHardware ? 12 : null,
            'explanation' => 'Additional premium capabilities become available with compatible hardware.',
        ];
    }

    /**
     * @param array<string, mixed>|null $compat
     */
    private function resolveReason(
        string $availability,
        ?RuntimeEngine $currentEngine,
        ?array $compat,
        RuntimeCapabilityClassification $classification,
    ): ?string {
        return match ($availability) {
            'ready' => sprintf('%s is operational.', $currentEngine?->displayName ?? 'Engine'),
            'not_installed' => RuntimeCapabilityClassification::Optional === $classification
                ? 'Optional capability — install when needed.'
                : 'Not installed — install from the Provision Center.',
            'unsupported_hardware' => $compat['humanReason'] ?? $currentEngine?->errorReason ?? 'Hardware requirements not met.',
            'blocked' => $compat['humanReason'] ?? $currentEngine?->errorReason ?? 'Engine blocked or misconfigured.',
            'disabled' => 'Experimental capability disabled.',
            default => null,
        };
    }

    /**
     * @param list<array<string, mixed>> $capabilities
     *
     * @return array<string, list<array<string, mixed>>>
     */
    private function groupByClassification(array $capabilities): array
    {
        $groups = [
            'core' => [],
            'optional' => [],
            'premium' => [],
            'experimental' => [],
            'deprecated' => [],
        ];

        foreach ($capabilities as $cap) {
            $key = (string) ($cap['classification'] ?? 'optional');
            if (!isset($groups[$key])) {
                $groups[$key] = [];
            }
            $groups[$key][] = $cap;
        }

        return $groups;
    }

    /**
     * @param list<array<string, mixed>> $capabilities
     */
    private function countReady(array $capabilities): int
    {
        return count(array_filter(
            $capabilities,
            static fn (array $cap): bool => ($cap['availability'] ?? '') === 'ready',
        ));
    }

    /**
     * @param list<array<string, mixed>> $capabilities
     */
    private function countInstalled(array $capabilities): int
    {
        return count(array_filter(
            $capabilities,
            static fn (array $cap): bool => !in_array($cap['availability'] ?? '', ['not_installed'], true),
        ));
    }

    /**
     * @param list<array<string, mixed>> $capabilities
     */
    private function countByAvailability(array $capabilities, string $availability): int
    {
        return count(array_filter(
            $capabilities,
            static fn (array $cap): bool => ($cap['availability'] ?? '') === $availability,
        ));
    }

    /**
     * @param list<array<string, mixed>> $capabilities
     *
     * @return array<string, mixed>
     */
    private function buildCoverageSection(
        string $label,
        array $capabilities,
        int $installed,
        int $total,
    ): array {
        $ready = $this->countReady($capabilities);
        $blocked = $this->countByAvailability($capabilities, 'blocked');
        $notInstalled = $this->countByAvailability($capabilities, 'not_installed');

        $status = match (true) {
            $total === 0 => 'ready',
            $ready === $total => 'ready',
            $notInstalled === $total => 'not_installed',
            $blocked > 0 => 'blocked',
            default => 'partial',
        };

        return [
            'label' => $label,
            'status' => $status,
            'readyCount' => $ready,
            'installedCount' => $installed,
            'totalCount' => $total,
            'blockedCount' => $blocked,
            'notInstalledCount' => $notInstalled,
            'capabilities' => $capabilities,
        ];
    }

    /**
     * @param list<array<string, mixed>> $capabilities
     *
     * @return array<string, mixed>
     */
    private function buildPremiumSection(array $capabilities, int $available, int $total): array
    {
        $blockedHardware = $this->countByAvailability($capabilities, 'unsupported_hardware');
        $blocked = $this->countByAvailability($capabilities, 'blocked');

        $status = match (true) {
            $total === 0 => 'ready',
            $available === $total => 'ready',
            $blockedHardware > 0 => 'unsupported_hardware',
            $blocked > 0 => 'blocked',
            default => 'partial',
        };

        return [
            'label' => 'Premium',
            'status' => $status,
            'availableCount' => $available,
            'totalCount' => $total,
            'blockedByHardwareCount' => $blockedHardware,
            'capabilities' => $capabilities,
        ];
    }
}
