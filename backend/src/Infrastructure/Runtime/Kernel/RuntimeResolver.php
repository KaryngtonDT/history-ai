<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Kernel;

use App\Application\Hardware\HardwareReportBuilder;
use App\Application\Runtime\PipelineStageCapabilityMapper;
use App\Application\Runtime\RuntimeResolverInterface;
use App\Domain\Engine\Engine;
use App\Domain\Engine\EngineCatalogCapability;
use App\Domain\Engine\EngineRepositoryInterface;
use App\Domain\Engine\SelectionMode;
use App\Domain\Hardware\HardwareRepositoryInterface;
use App\Domain\Runtime\EngineExecutionPlan;
use App\Domain\Runtime\ResolvedEngine;
use App\Domain\Runtime\RuntimeFallbackPlan;
use App\Domain\Runtime\RuntimeResolveContext;
use App\Domain\Runtime\RuntimeResolveReason;
use App\Domain\Runtime\RuntimeResolveRequest;
use App\Domain\Runtime\RuntimeRepositoryInterface;
use App\Infrastructure\Runtime\Catalog\EngineCatalogDefinitions;
use App\Infrastructure\Runtime\Compatibility\RuntimeCompatibilityService;
use App\Infrastructure\Runtime\Intelligence\RecommendationEngine;

final class RuntimeResolver implements RuntimeResolverInterface
{
    public function __construct(
        private readonly RuntimeRepositoryInterface $runtimeRepository,
        private readonly EngineRepositoryInterface $engineRepository,
        private readonly RecommendationEngine $recommendationEngine,
        private readonly HardwareRepositoryInterface $hardwareRepository,
        private readonly HardwareReportBuilder $hardwareReportBuilder,
        private readonly RuntimeCompatibilityService $compatibilityService,
        private readonly EngineAdapterRegistry $adapterRegistry,
    ) {
    }

    public function resolve(RuntimeResolveRequest $request): EngineExecutionPlan
    {
        $resolved = $this->resolveEngineMetadata($request->capability, $request->context);

        return new EngineExecutionPlan(
            resolvedEngine: $resolved,
            planId: bin2hex(random_bytes(16)),
            adapterKey: $resolved->adapterKey,
            parameters: [
                'executionProfile' => $resolved->executionProfile,
                'language' => $request->context->language,
                'durationSeconds' => $request->context->durationSeconds,
            ],
            fallbackPlan: $resolved->fallback,
        );
    }

    public function resolveCapability(
        EngineCatalogCapability $capability,
        RuntimeResolveContext $context = new RuntimeResolveContext(),
    ): EngineExecutionPlan {
        return $this->resolve(new RuntimeResolveRequest($capability, $context));
    }

    public function resolveEngineMetadata(
        EngineCatalogCapability $capability,
        RuntimeResolveContext $context = new RuntimeResolveContext(),
    ): ResolvedEngine {
        $config = $this->runtimeRepository->getConfiguration();
        $capKey = $capability->value;
        $engines = $this->engineRepository->findByCapability($capability);
        $default = EngineCatalogDefinitions::defaultForCapability($capability);
        $defaultId = $default?->id ?? '';

        $hardware = $this->hardwareRepository->overview();
        $profile = is_array($hardware['profile'] ?? null) ? $hardware['profile'] : [];
        $recommendedPipeline = $this->hardwareReportBuilder->recommendedPipeline(
            $this->hardwareRepository->detect()->profile,
        );
        $pipelineKey = PipelineStageCapabilityMapper::hardwarePipelineKey($capability);
        $hardwareRecommendedId = null !== $pipelineKey
            ? ($recommendedPipeline[$pipelineKey] ?? null)
            : null;

        $recommendations = $this->recommendationEngine->recommend($config);
        $profileRecommendedId = null;
        foreach ($recommendations as $rec) {
            if (($rec['capability'] ?? '') === $capKey) {
                $profileRecommendedId = is_string($rec['recommendedEngineId'] ?? null)
                    ? $rec['recommendedEngineId']
                    : null;
                break;
            }
        }

        $configuredEngine = $this->findConfiguredEngine($engines);

        [$engineId, $reason, $confidence] = $this->pickEngineId(
            $capKey,
            $config->selectionMode,
            $config->manualSelections[$capKey] ?? null,
            $context->preferredEngineId,
            $hardwareRecommendedId,
            $profileRecommendedId,
            $configuredEngine?->id,
            $defaultId,
        );

        $engine = $this->findEngineById($engines, $engineId) ?? $this->engineRepository->findById($engineId);
        $compat = $this->compatibilityService->evaluateEngine($engineId);
        $fallbackEngine = $this->resolveFallback($engines, $compat?->recommendedAlternative);

        $blocked = null !== $compat && 'ready' !== ($compat->status ?? '') && !$engine?->isReady();
        $executable = $engine?->isReady() ?? false;

        if ($blocked && null !== $fallbackEngine && $fallbackEngine->isReady()) {
            $engineId = $fallbackEngine->id;
            $engine = $fallbackEngine;
            $reason = RuntimeResolveReason::Fallback;
            $confidence = max(0.5, $confidence - 0.2);
            $executable = true;
            $blocked = false;
        }

        $fallbackPlan = null !== $fallbackEngine && $fallbackEngine->id !== $engineId
            ? new RuntimeFallbackPlan(
                $fallbackEngine->id,
                $this->adapterRegistry->adapterKeyForEngine($fallbackEngine->id),
                RuntimeResolveReason::Fallback,
            )
            : null;

        return new ResolvedEngine(
            engineId: $engineId,
            displayName: $engine?->displayName ?? $default?->displayName ?? $engineId,
            capability: $capability,
            adapterKey: $this->adapterRegistry->adapterKeyForEngine($engineId),
            reason: $reason,
            confidence: $confidence,
            executable: $executable,
            blocked: $blocked && !$executable,
            blockedReason: $compat?->humanReason ?? $engine?->errorReason,
            provider: $compat?->provider->value ?? 'docker',
            executionProfile: $config->profile->value,
            fallback: $fallbackPlan,
        );
    }

    public function capabilitySelectionView(EngineCatalogCapability $capability): array
    {
        $resolved = $this->resolveEngineMetadata($capability);
        $engines = $this->engineRepository->findByCapability($capability);
        $default = EngineCatalogDefinitions::defaultForCapability($capability);
        $readyEngines = array_values(array_filter($engines, static fn (Engine $e): bool => $e->isReady()));
        $installedIds = array_map(static fn (Engine $e): string => $e->id, $readyEngines);

        $hardware = $this->hardwareRepository->overview();
        $pipelineKey = PipelineStageCapabilityMapper::hardwarePipelineKey($capability);
        $recommendedPipeline = $this->hardwareReportBuilder->recommendedPipeline(
            $this->hardwareRepository->detect()->profile,
        );
        $recommendedId = null !== $pipelineKey
            ? ($recommendedPipeline[$pipelineKey] ?? $default?->id)
            : $default?->id;

        $recommendedEngine = $this->findEngineById($engines, (string) $recommendedId);

        return [
            'capability' => $capability->value,
            'label' => $capability->label(),
            'videoPipeline' => $capability->isVideoPipeline(),
            'referenceEngineId' => $default?->id,
            'referenceDisplayName' => $default?->displayName,
            'recommendedEngineId' => $recommendedId,
            'recommendedDisplayName' => $recommendedEngine?->displayName ?? $recommendedId,
            'currentEngineId' => $resolved->engineId,
            'currentDisplayName' => $resolved->displayName,
            'installedEngineIds' => $installedIds,
            'adapterKey' => $resolved->adapterKey,
            'blockedReason' => $resolved->blockedReason,
            'blocked' => $resolved->blocked,
            'executable' => $resolved->executable,
            'benchmark' => null,
            'health' => $resolved->executable ? 'ready' : 'blocked',
            'resolvedEngine' => $resolved->toArray(),
        ];
    }

    public function capabilities(): array
    {
        $items = [];
        foreach (EngineCatalogCapability::cases() as $capability) {
            $items[] = [
                'capability' => $capability->value,
                'label' => $capability->label(),
                'videoPipeline' => $capability->isVideoPipeline(),
                'selectionView' => $this->capabilitySelectionView($capability),
            ];
        }

        return $items;
    }

    public function selection(): array
    {
        $config = $this->runtimeRepository->getConfiguration();

        return [
            'profile' => $config->profile->value,
            'profileLabel' => $config->profile->label(),
            'selectionMode' => $config->selectionMode->value,
            'manualSelections' => $config->manualSelections,
            'resolved' => array_map(
                fn (EngineCatalogCapability $cap): array => $this->resolveEngineMetadata($cap)->toArray(),
                array_filter(
                    EngineCatalogCapability::cases(),
                    static fn (EngineCatalogCapability $c): bool => $c->isVideoPipeline(),
                ),
            ),
        ];
    }

    /**
     * @param list<Engine> $engines
     */
    private function findConfiguredEngine(array $engines): ?Engine
    {
        foreach ($engines as $engine) {
            if ($engine->configured) {
                return $engine;
            }
        }

        return null;
    }

    /**
     * @param list<Engine> $engines
     */
    private function findEngineById(array $engines, string $engineId): ?Engine
    {
        foreach ($engines as $engine) {
            if ($engine->id === $engineId) {
                return $engine;
            }
        }

        return null;
    }

    /**
     * @param list<Engine> $engines
     */
    private function resolveFallback(array $engines, ?string $alternativeId): ?Engine
    {
        if (null === $alternativeId || '' === $alternativeId) {
            return null;
        }

        return $this->findEngineById($engines, $alternativeId)
            ?? $this->engineRepository->findById($alternativeId);
    }

    /**
     * @return array{0: string, 1: RuntimeResolveReason, 2: float}
     */
    private function pickEngineId(
        string $capKey,
        SelectionMode $selectionMode,
        ?string $manualSelection,
        ?string $plannerPreferred,
        ?string $hardwareRecommended,
        ?string $profileRecommended,
        ?string $opsConfiguredId,
        string $defaultId,
    ): array {
        if (SelectionMode::Manual === $selectionMode && is_string($manualSelection) && '' !== $manualSelection) {
            return [$manualSelection, RuntimeResolveReason::UserSelection, 1.0];
        }

        if (is_string($plannerPreferred) && '' !== $plannerPreferred) {
            return [$plannerPreferred, RuntimeResolveReason::PlannerContext, 0.95];
        }

        if (is_string($hardwareRecommended) && '' !== $hardwareRecommended) {
            return [$hardwareRecommended, RuntimeResolveReason::HardwareRecommended, 0.9];
        }

        if (is_string($profileRecommended) && '' !== $profileRecommended) {
            return [$profileRecommended, RuntimeResolveReason::ProfileRecommended, 0.85];
        }

        if (is_string($opsConfiguredId) && '' !== $opsConfiguredId) {
            return [$opsConfiguredId, RuntimeResolveReason::OpsBootstrap, 0.8];
        }

        if ('' !== $defaultId) {
            return [$defaultId, RuntimeResolveReason::CatalogDefault, 0.7];
        }

        return [$capKey, RuntimeResolveReason::CatalogDefault, 0.5];
    }
}
