<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Management;

use App\Application\EngineAnalytics\EngineStatisticsAggregator;
use App\Application\Runtime\RuntimeResolverInterface;
use App\Domain\Engine\CapabilitySelectionMode;
use App\Domain\Engine\Engine;
use App\Domain\Engine\EngineCatalogCapability;
use App\Domain\Engine\EngineRepositoryInterface;
use App\Domain\Runtime\RuntimeRepositoryInterface;
use App\Infrastructure\Runtime\Catalog\EngineCatalogDefinitions;
use App\Infrastructure\Runtime\Compatibility\RuntimeCompatibilityService;
use App\Infrastructure\Runtime\Discovery\EngineDiscovery;
use App\Infrastructure\Runtime\Health\HealthMonitor;
use App\Infrastructure\Runtime\Intelligence\RecommendationEngine;
use App\Infrastructure\Runtime\Lifecycle\RuntimeDependencyManager;
use App\Infrastructure\Runtime\Lifecycle\RuntimeModelManager;
use App\Infrastructure\Runtime\Lifecycle\RuntimeVersionManager;

final class RuntimeEngineManagementAssembler
{
    public function __construct(
        private readonly EngineDiscovery $engineDiscovery,
        private readonly EngineRepositoryInterface $engineRepository,
        private readonly RuntimeRepositoryInterface $runtimeRepository,
        private readonly RuntimeResolverInterface $runtimeResolver,
        private readonly RecommendationEngine $recommendationEngine,
        private readonly RuntimeCompatibilityService $compatibilityService,
        private readonly HealthMonitor $healthMonitor,
        private readonly EngineStatisticsAggregator $statisticsAggregator,
        private readonly RuntimeVersionManager $versionManager,
        private readonly RuntimeModelManager $modelManager,
        private readonly RuntimeDependencyManager $dependencyManager,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function assemble(): array
    {
        $config = $this->runtimeRepository->getConfiguration();
        $discovered = $this->engineDiscovery->discover();
        $discoveredById = [];

        foreach ($discovered as $engine) {
            $discoveredById[$engine->id] = $engine;
        }

        $analyticsByEngine = [];
        foreach ($this->statisticsAggregator->aggregateEngines() as $stat) {
            $analyticsByEngine[(string) ($stat['engineId'] ?? '')] = $stat;
        }

        $capabilities = [];

        foreach (EngineCatalogCapability::cases() as $capability) {
            $selectionView = $this->runtimeResolver->capabilitySelectionView($capability);
            $mode = $config->capabilityMode($capability->value);
            $catalogEngines = $this->engineRepository->findByCapability($capability);
            $default = EngineCatalogDefinitions::defaultForCapability($capability);

            $engines = [];

            foreach ($catalogEngines as $catalogEngine) {
                if ($config->isEngineDisabled($catalogEngine->id)) {
                    continue;
                }

                $live = $discoveredById[$catalogEngine->id] ?? $catalogEngine;
                $engines[] = $this->buildEngineCard(
                    $live,
                    $capability,
                    $selectionView,
                    $default?->id,
                    $analyticsByEngine[$catalogEngine->id] ?? null,
                );
            }

            $capabilities[] = [
                'capability' => $capability->value,
                'label' => $capability->label(),
                'videoPipeline' => $capability->isVideoPipeline(),
                'selectionMode' => $mode->value,
                'selectionModeLabel' => $mode->label(),
                'recommendedEngineId' => $selectionView['recommendedEngineId'] ?? null,
                'currentEngineId' => $selectionView['currentEngineId'] ?? null,
                'referenceEngineId' => $default?->id,
                'engines' => $engines,
            ];
        }

        return [
            'principle' => 'Runtime decides. Worker executes. UI observes.',
            'configuration' => $config->toArray(),
            'recommendations' => $this->recommendationEngine->recommend($config),
            'capabilities' => $capabilities,
            'at' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ];
    }

    /**
     * @param array<string, mixed>      $selectionView
     * @param array<string, mixed>|null $analytics
     *
     * @return array<string, mixed>
     */
    private function buildEngineCard(
        Engine $engine,
        EngineCatalogCapability $capability,
        array $selectionView,
        ?string $referenceEngineId,
        ?array $analytics,
    ): array {
        $compat = $this->compatibilityService->evaluateEngine($engine->id);
        $health = $this->healthMonitor->heartbeat();
        $isCurrent = ($selectionView['currentEngineId'] ?? '') === $engine->id;
        $isRecommended = ($selectionView['recommendedEngineId'] ?? '') === $engine->id;
        $isReference = $referenceEngineId === $engine->id;

        return [
            'engineId' => $engine->id,
            'displayName' => $engine->displayName,
            'capability' => $capability->value,
            'capabilityLabel' => $capability->label(),
            'provider' => $compat?->provider->value ?? 'docker',
            'version' => $this->versionManager->version($engine->id),
            'isReference' => $isReference,
            'isRecommended' => $isRecommended,
            'isCurrent' => $isCurrent,
            'installed' => $engine->installed,
            'ready' => $engine->isReady(),
            'blocked' => !$engine->isReady() && ($compat?->status ?? '') !== 'ready',
            'misconfigured' => $engine->configured && !$engine->isReady(),
            'mock' => 'mock' === $engine->executionMode->value,
            'status' => $engine->runtimeStatus->value,
            'mode' => $engine->executionMode->value,
            'hardwareCompatibility' => $compat?->toArray(),
            'runtimeHealth' => $health->status->value,
            'benchmarkScore' => $analytics['relativeSpeedLabel'] ?? null,
            'averageDurationSeconds' => $analytics['averageDurationSeconds'] ?? null,
            'averageAccuracy' => isset($analytics['successRate']) ? round((float) $analytics['successRate'] * 100, 1) : null,
            'executionCount' => $analytics['executionCount'] ?? 0,
            'model' => $this->modelManager->modelInfo($engine->id),
            'dependencies' => $this->dependencyManager->dependencies($engine->id),
            'blockedReason' => $engine->errorReason ?? $compat?->humanReason,
            'documentationUrl' => $engine->documentationUrl,
            'documentationPath' => $engine->documentationPath,
            'installCommand' => $engine->installCommand,
            'autoProvisionSupported' => $engine->autoProvisionSupported,
        ];
    }
}
