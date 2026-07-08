<?php

declare(strict_types=1);

namespace App\Infrastructure\RuntimeDashboard;

use App\Application\Platform\PlatformHealthCheckerInterface;
use App\Application\Runtime\RuntimePlatformInterface;
use App\Application\RuntimeDashboard\PlatformScoreCalculator;
use App\Application\RuntimeDashboard\RuntimeDashboardInterface;
use App\Application\RuntimeDashboard\RuntimeScoreCalculator;
use App\Domain\Engine\EngineCatalogCapability;
use App\Domain\Engine\EngineCatalogTier;
use App\Domain\Runtime\RuntimeRepositoryInterface;
use App\Infrastructure\Runtime\Benchmark\BenchmarkRunner;
use App\Infrastructure\Runtime\Catalog\CapabilityMaturityRegistry;
use App\Infrastructure\Runtime\Catalog\EngineCatalogDefinitions;
use App\Infrastructure\Runtime\Catalog\EngineRequirementMatrix;
use App\Infrastructure\Runtime\Catalog\RuntimeCapabilityClassificationRegistry;
use App\Infrastructure\Runtime\Compatibility\RuntimeCompatibilityService;
use App\Infrastructure\Runtime\Health\RuntimePlatformHealthService;
use App\Infrastructure\Runtime\Provisioning\EngineProvisioningCatalog;

final class RuntimeDashboardAssembler implements RuntimeDashboardInterface
{
    public function __construct(
        private readonly RuntimePlatformInterface $platform,
        private readonly RuntimeCompatibilityService $compatibilityService,
        private readonly BenchmarkRunner $benchmarkRunner,
        private readonly RuntimeRepositoryInterface $runtimeRepository,
        private readonly PlatformHealthCheckerInterface $platformHealthChecker,
        private readonly RuntimeScoreCalculator $runtimeScoreCalculator,
        private readonly PlatformScoreCalculator $platformScoreCalculator,
        private readonly RuntimePlatformHealthService $platformHealthService,
        private readonly string $projectDir,
    ) {
    }

    public function dashboard(): array
    {
        $platformHealth = $this->platformHealthService->evaluate();
        $coreHealth = is_array($platformHealth['coreHealth'] ?? null) ? $platformHealth['coreHealth'] : [];
        $readiness = $this->platform->readiness();
        $hardware = $this->platform->hardware();
        $compatibility = $this->platform->compatibility();
        $recommendations = $this->platform->recommendations();
        $provisioningPlan = $this->platform->provisioningPlan();
        $health = $this->platform->health();

        $engines = is_array($readiness['engines'] ?? null) ? $readiness['engines'] : [];
        $compatEngines = is_array($compatibility['engines'] ?? null) ? $compatibility['engines'] : [];
        $compatById = [];
        foreach ($compatEngines as $row) {
            if (is_array($row) && isset($row['engineId'])) {
                $compatById[$row['engineId']] = $row;
            }
        }

        $hardwareCompatibleIds = array_values(array_filter(
            array_keys($compatById),
            static fn (string $id): bool => (bool) ($compatById[$id]['hardwareCompatible'] ?? false),
        ));

        $readyIds = is_array($compatibility['readyNow'] ?? null) ? $compatibility['readyNow'] : [];
        $premiumIds = $this->premiumEngineIds();
        $premiumReady = array_values(array_intersect($premiumIds, $readyIds));
        $compatibleReady = array_values(array_intersect($hardwareCompatibleIds, $readyIds));

        $scoreInputs = $this->buildScoreInputs(
            $readiness,
            $health,
            $compatibility,
            $hardwareCompatibleIds,
            $compatibleReady,
            $provisioningPlan,
            $coreHealth,
        );
        $runtimeScore = $this->runtimeScoreCalculator->calculate($scoreInputs);
        $scoreModel = $this->runtimeScoreCalculator->calculateScoreModel($scoreInputs, $platformHealth);
        $coreScore = (float) ($coreHealth['percent'] ?? $runtimeScore->score);

        $platformReadiness = $this->platformHealthChecker->productionReadiness();
        $platformScore = $this->platformScoreCalculator->calculate([
            'runtime' => $coreScore,
            'shadow' => $this->shadowScore($platformReadiness),
            'storage' => $this->checkScore($platformReadiness, 'storageWritable'),
            'worker' => $this->checkScore($platformReadiness, 'worker'),
            'api' => 100.0,
            'docker' => $this->checkScore($platformReadiness, 'dockerProduction'),
            'postgres' => $this->checkScore($platformReadiness, 'postgres'),
            'documentation' => $scoreInputs['documentation'],
        ]);

        $capabilities = $this->buildCapabilities($engines, $compatById, $hardware, $recommendations, $platformHealth);
        $capabilityScores = $this->buildCapabilityScores($capabilities);
        $premiumFeatures = $this->buildPremiumFeatures($compatById);
        $timeline = $this->buildTimeline();
        $shadowCommentary = $this->buildShadowCommentary(
            $coreScore,
            $compatibility,
            $hardware,
            $premiumFeatures,
            $platformHealth,
        );

        $lastValidation = $this->lastValidationSummary();

        return [
            'title' => 'Lumen Runtime Health',
            'generatedAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'overallRuntimeScore' => [
                ...$runtimeScore->toArray(),
                'score' => $coreScore,
                'summary' => ($coreHealth['status'] ?? '') === 'ready'
                    ? 'Core Runtime is healthy. Optional and premium capabilities are tracked separately.'
                    : 'Core Runtime needs attention. Optional capabilities do not affect core health.',
            ],
            'scoreModel' => $scoreModel->toArray(),
            'platformHealth' => $platformHealth,
            'platformScore' => $platformScore,
            'summary' => [
                'overallHealth' => $coreScore,
                'coreHealthPercent' => $coreScore,
                'coreStatus' => $coreHealth['status'] ?? 'fail',
                'hardwareProfile' => $hardware['profile']['type'] ?? 'unknown',
                'hardwareProfileLabel' => $hardware['profile']['label'] ?? 'Unknown',
                'runtimeStatus' => strtoupper((string) ($readiness['status'] ?? 'unknown')),
                'provisioningPercent' => $scoreInputs['provisioning'],
                'compatibleEnginesReady' => count($compatibleReady),
                'compatibleEnginesTotal' => count($hardwareCompatibleIds),
                'premiumEnginesReady' => count($premiumReady),
                'premiumEnginesTotal' => count($premiumIds),
                'benchmarksPassedPercent' => $scoreInputs['benchmarks'],
                'counters' => $platformHealth['counters'] ?? [],
                'lastValidation' => $lastValidation,
            ],
            'capabilityStatuses' => $capabilities,
            'capabilityScores' => $capabilityScores,
            'hardware' => $this->buildHardwarePanel($hardware),
            'engineRecommendations' => $this->buildEngineRecommendations($hardware, $engines, $recommendations),
            'premiumFeatures' => $premiumFeatures,
            'timeline' => $timeline,
            'warnings' => $this->buildWarnings($compatById),
            'recommendations' => $this->buildPipelineRecommendations($hardware, $compatibleReady, $premiumFeatures),
            'shadowCommentary' => $shadowCommentary,
            'maturity' => CapabilityMaturityRegistry::all(),
        ];
    }

    /**
     * @param array<string, mixed> $readiness
     * @param array<string, mixed> $health
     * @param array<string, mixed> $compatibility
     * @param list<string> $hardwareCompatibleIds
     * @param list<string> $compatibleReady
     * @param array<string, mixed> $provisioningPlan
     * @param array<string, mixed> $coreHealth
     *
     * @return array{
     *   runtimeHealth: float,
     *   compatibleInstalled: float,
     *   engineTests: float,
     *   benchmarks: float,
     *   documentation: float,
     *   hardwareCompatibility: float,
     *   provisioning: float
     * }
     */
    private function buildScoreInputs(
        array $readiness,
        array $health,
        array $compatibility,
        array $hardwareCompatibleIds,
        array $compatibleReady,
        array $provisioningPlan,
        array $coreHealth = [],
    ): array {
        $totalEngines = (int) ($readiness['totalCount'] ?? 0);
        $readyCount = (int) ($readiness['readyCount'] ?? 0);
        $runtimeHealth = (float) ($coreHealth['percent'] ?? 0.0);
        if ($runtimeHealth <= 0.0) {
            $runtimeHealth = $totalEngines > 0 ? ($readyCount / $totalEngines) * 100 : 0.0;
        }

        $compatibleTotal = count($hardwareCompatibleIds);
        $compatibleInstalled = $compatibleTotal > 0
            ? (count($compatibleReady) / $compatibleTotal) * 100
            : 0.0;

        $compatEngineRows = is_array($compatibility['engines'] ?? null) ? $compatibility['engines'] : [];
        $hardwareCompatCount = count(array_filter(
            $compatEngineRows,
            static fn (array $row): bool => (bool) ($row['hardwareCompatible'] ?? false),
        ));
        $hardwareCompatibility = count($compatEngineRows) > 0
            ? ($hardwareCompatCount / count($compatEngineRows)) * 100
            : 0.0;

        $benchmarkHistory = $this->benchmarkRunner->history();
        $recentBenchmarks = array_slice($benchmarkHistory, -50);
        $benchmarkPass = 0;
        foreach ($recentBenchmarks as $row) {
            if (($row['ok'] ?? false) === true) {
                ++$benchmarkPass;
            }
        }
        $benchmarks = [] === $recentBenchmarks ? 0.0 : ($benchmarkPass / count($recentBenchmarks)) * 100;

        $engineTests = (float) ($health['score'] ?? $runtimeHealth);

        $toProvision = is_array($provisioningPlan['toProvision'] ?? null) ? $provisioningPlan['toProvision'] : [];
        $provisionTotal = count($compatibleReady) + count($toProvision);
        $provisioning = $provisionTotal > 0 ? (count($compatibleReady) / $provisionTotal) * 100 : $compatibleInstalled;

        return [
            'runtimeHealth' => round($runtimeHealth, 1),
            'compatibleInstalled' => round($compatibleInstalled, 1),
            'engineTests' => round($engineTests, 1),
            'benchmarks' => round($benchmarks, 1),
            'documentation' => round($this->documentationScore(), 1),
            'hardwareCompatibility' => round($hardwareCompatibility, 1),
            'provisioning' => round($provisioning, 1),
        ];
    }

    private function documentationScore(): float
    {
        $paths = [
            'docs/architecture/CAPABILITY_PLATFORM_VISION.md',
            'docs/architecture/RUNTIME_DASHBOARD.md',
            'docs/operations/ENGINE_INSTALLATION.md',
            'docs/operations/ENGINE_PROVISIONING.md',
            'docs/operations/LATENTSYNC_INSTALLATION.md',
        ];

        $found = 0;
        foreach ($paths as $relative) {
            if (is_file($this->projectDir . '/' . $relative)) {
                ++$found;
            }
        }

        return ($found / max(1, count($paths))) * 100;
    }

    /**
     * @return list<string>
     */
    private function premiumEngineIds(): array
    {
        $ids = [];
        foreach (EngineCatalogDefinitions::all() as $definition) {
            $req = EngineRequirementMatrix::findByEngineId($definition->id);
            if (
                EngineCatalogTier::PremiumNvidia === $definition->tier
                || (null !== $req && ($req->cudaRequired || $req->nvencRequired))
            ) {
                $ids[] = $definition->id;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @param list<array<string, mixed>> $engines
     * @param array<string, array<string, mixed>> $compatById
     * @param array<string, mixed> $hardware
     * @param list<array<string, mixed>> $recommendations
     * @param array<string, mixed> $platformHealth
     *
     * @return list<array<string, mixed>>
     */
    private function buildCapabilities(
        array $engines,
        array $compatById,
        array $hardware,
        array $recommendations,
        array $platformHealth,
    ): array {
        $classifiedByCap = [];
        foreach ($platformHealth['capabilities'] ?? [] as $capState) {
            if (is_array($capState) && isset($capState['capability'])) {
                $classifiedByCap[$capState['capability']] = $capState;
            }
        }

        $recommendedPipeline = is_array($hardware['recommendedPipeline'] ?? null)
            ? $hardware['recommendedPipeline']
            : [];

        $recByCapability = [];
        foreach ($recommendations as $rec) {
            if (is_array($rec) && isset($rec['capability'])) {
                $recByCapability[$rec['capability']] = $rec;
            }
        }

        $byCapability = [];
        foreach ($engines as $engine) {
            if (!is_array($engine) || !isset($engine['capability'])) {
                continue;
            }
            $byCapability[$engine['capability']][] = $engine;
        }

        $benchmarkByEngine = $this->latestBenchmarksByEngine();

        $items = [];
        foreach (EngineCatalogCapability::cases() as $capability) {
            $capKey = $capability->value;
            $capEngines = $byCapability[$capKey] ?? [];
            $default = EngineCatalogDefinitions::defaultForCapability($capability);
            $referenceId = $default?->id;
            $pipelineKey = $this->pipelineKeyFor($capability);
            $recommendedId = $recommendedPipeline[$pipelineKey] ?? ($recByCapability[$capKey]['recommendedEngineId'] ?? $referenceId);

            $configured = array_values(array_filter($capEngines, static fn (array $e): bool => (bool) ($e['configured'] ?? false)));
            $currentId = $configured[0]['id'] ?? $recommendedId;
            $readyInCap = array_values(array_filter(
                $capEngines,
                static fn (array $e): bool => 'ready' === ($e['status'] ?? ''),
            ));

            $status = match (true) {
                [] === $capEngines => 'unknown',
                $capability->isVideoPipeline() && [] !== $configured && [] !== $readyInCap && count($readyInCap) < count($configured) => 'partial',
                [] === $readyInCap && !$capability->isVideoPipeline() => 'not_installed',
                [] === $readyInCap => 'blocked',
                count($readyInCap) === count($capEngines) => 'ready',
                [] !== $readyInCap => 'partial',
                default => 'blocked',
            };

            $installedIds = array_map(
                static fn (array $e): string => (string) $e['id'],
                $readyInCap,
            );

            $currentEngine = $this->findEngine($capEngines, is_string($currentId) ? $currentId : null);
            $recommendedEngine = $this->findEngine($capEngines, is_string($recommendedId) ? $recommendedId : null);
            $currentCompat = is_string($currentId) ? ($compatById[$currentId] ?? null) : null;
            $scorePercent = count($capEngines) > 0
                ? (int) round((count($readyInCap) / max(1, count($capEngines))) * 100)
                : 0;

            $classified = $classifiedByCap[$capKey] ?? null;
            $meta = RuntimeCapabilityClassificationRegistry::for($capability);

            $items[] = [
                'capability' => $capKey,
                'label' => $capability->label(),
                'classification' => $classified['classification'] ?? $meta->classification->value,
                'classificationLabel' => $classified['classificationLabel'] ?? $meta->classification->label(),
                'required' => $classified['required'] ?? $meta->required,
                'availability' => $classified['availability'] ?? null,
                'availabilityLabel' => $classified['availabilityLabel'] ?? null,
                'reason' => $classified['reason'] ?? null,
                'futureHardware' => $classified['futureHardware'] ?? null,
                'status' => $status,
                'statusLabel' => strtoupper(str_replace('_', ' ', $status)),
                'videoPipeline' => $capability->isVideoPipeline(),
                'referenceEngineId' => $referenceId,
                'referenceDisplayName' => $default?->displayName,
                'recommendedEngineId' => $recommendedId,
                'recommendedDisplayName' => $recommendedEngine['displayName'] ?? $recommendedId,
                'currentEngineId' => $currentId,
                'currentDisplayName' => $currentEngine['displayName'] ?? $currentId,
                'installedEngineIds' => $installedIds,
                'readyCount' => count($readyInCap),
                'engineCount' => count($capEngines),
                'score' => $scorePercent,
                'blockedReason' => $currentCompat['humanReason'] ?? null,
                'hardwareCompatible' => $currentCompat['hardwareCompatible'] ?? null,
                'provider' => $currentCompat['provider'] ?? null,
                'providerLabel' => $currentCompat['providerLabel'] ?? null,
                'benchmark' => is_string($currentId) ? ($benchmarkByEngine[$currentId] ?? null) : null,
                'health' => ($currentEngine ?? [])['status'] ?? null,
                'improvement' => $currentCompat['recommendedAlternative'] ?? null,
                'alternative' => $currentCompat['recommendedAlternative'] ?? null,
            ];
        }

        return $items;
    }

    /**
     * @param list<array<string, mixed>> $engines
     *
     * @return array<string, mixed>|null
     */
    private function findEngine(array $engines, ?string $engineId): ?array
    {
        if (null === $engineId) {
            return null;
        }

        foreach ($engines as $engine) {
            if (is_array($engine) && ($engine['id'] ?? '') === $engineId) {
                return $engine;
            }
        }

        return null;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function latestBenchmarksByEngine(): array
    {
        $byEngine = [];
        foreach ($this->benchmarkRunner->history() as $row) {
            if (!is_array($row) || !isset($row['engineId'])) {
                continue;
            }
            $id = (string) $row['engineId'];
            if (!isset($byEngine[$id])) {
                $byEngine[$id] = [
                    'ok' => (bool) ($row['ok'] ?? false),
                    'status' => ($row['ok'] ?? false) ? 'PASS' : 'FAIL',
                    'at' => $row['at'] ?? null,
                ];
            }
        }

        return $byEngine;
    }

    /**
     * @param list<array<string, mixed>> $capabilities
     *
     * @return list<array<string, mixed>>
     */
    private function buildCapabilityScores(array $capabilities): array
    {
        $scores = [];
        foreach ($capabilities as $cap) {
            $ready = (int) ($cap['readyCount'] ?? 0);
            $total = max(1, (int) ($cap['engineCount'] ?? 1));
            $percent = round(($ready / $total) * 100, 0);
            $reason = $cap['reason'] ?? null;
            if (null === $reason && ($cap['status'] ?? '') === 'partial') {
                $reason = 'Premium engine unavailable on current hardware';
            }
            if (null === $reason && ($cap['status'] ?? '') === 'not_installed') {
                $reason = 'Optional capability — install when needed';
            }

            $scores[] = [
                'capability' => $cap['capability'],
                'label' => $cap['label'],
                'classification' => $cap['classification'] ?? null,
                'score' => $percent,
                'reason' => $reason,
            ];
        }

        return $scores;
    }

    /**
     * @param array<string, mixed> $hardware
     *
     * @return array<string, mixed>
     */
    private function buildHardwarePanel(array $hardware): array
    {
        $caps = is_array($hardware['capabilities'] ?? null) ? $hardware['capabilities'] : [];
        $ramTotal = (float) ($caps['ramTotalGb'] ?? 0);
        $ramAvail = (float) ($caps['ramAvailableGb'] ?? $ramTotal);
        $diskFree = (float) ($caps['diskFreeGb'] ?? 0);

        return [
            'profile' => $hardware['profile'] ?? [],
            'cpuModel' => $caps['cpuModel'] ?? 'unknown',
            'gpuName' => $caps['gpuName'] ?? 'none',
            'gpuVendor' => $caps['gpuVendor'] ?? null,
            'cudaAvailable' => (bool) ($caps['cudaAvailable'] ?? false),
            'rocmAvailable' => (bool) ($caps['rocmAvailable'] ?? false),
            'directMlAvailable' => (bool) ($caps['directMlAvailable'] ?? false),
            'dockerGpuAccess' => (bool) ($caps['dockerGpuAccess'] ?? false),
            'wsl2' => (bool) ($caps['wsl2'] ?? false),
            'ramTotalGb' => $ramTotal,
            'ramAvailableGb' => $ramAvail,
            'ramUtilization' => $ramTotal > 0 ? round(($ramAvail / $ramTotal) * 100, 0) : 0,
            'diskFreeGb' => $diskFree,
            'diskUtilization' => $diskFree > 0 ? min(100, (int) round($diskFree / 10)) : 0,
            'ffmpegAvailable' => (bool) ($caps['ffmpegAvailable'] ?? false),
            'ollamaAvailable' => (bool) ($caps['ollamaAvailable'] ?? false),
            'pythonVersion' => $caps['pythonVersion'] ?? null,
            'recommendedPipeline' => $hardware['recommendedPipeline'] ?? [],
        ];
    }

    /**
     * @param array<string, mixed> $hardware
     * @param list<array<string, mixed>> $engines
     * @param list<array<string, mixed>> $recommendations
     *
     * @return list<array<string, mixed>>
     */
    private function buildEngineRecommendations(array $hardware, array $engines, array $recommendations): array
    {
        $pipeline = is_array($hardware['recommendedPipeline'] ?? null) ? $hardware['recommendedPipeline'] : [];
        $displayNames = [];
        foreach ($engines as $engine) {
            if (is_array($engine) && isset($engine['id'], $engine['displayName'])) {
                $displayNames[$engine['id']] = $engine['displayName'];
            }
        }

        $items = [];
        foreach ($recommendations as $rec) {
            if (!is_array($rec)) {
                continue;
            }
            $cap = (string) ($rec['capability'] ?? '');
            $pipelineKey = $this->pipelineKeyFromCapability($cap);
            $referenceId = $pipeline[$pipelineKey] ?? null;
            $recommendedId = $rec['recommendedEngineId'] ?? $referenceId;
            $currentId = $rec['requestedEngineId'] ?? $recommendedId;

            $items[] = [
                'capability' => $cap,
                'label' => $rec['label'] ?? $cap,
                'referenceEngineId' => $referenceId,
                'referenceDisplayName' => $referenceId ? ($displayNames[$referenceId] ?? $referenceId) : null,
                'recommendedEngineId' => $recommendedId,
                'recommendedDisplayName' => $recommendedId ? ($displayNames[$recommendedId] ?? $recommendedId) : null,
                'currentEngineId' => $currentId,
                'currentDisplayName' => $currentId ? ($displayNames[$currentId] ?? $currentId) : null,
                'reason' => $rec['reason'] ?? 'Based on hardware profile',
            ];
        }

        return $items;
    }

    /**
     * @param array<string, array<string, mixed>> $compatById
     *
     * @return list<array<string, mixed>>
     */
    private function buildPremiumFeatures(array $compatById): array
    {
        $items = [];
        foreach ($this->premiumEngineIds() as $engineId) {
            $compat = $compatById[$engineId] ?? null;
            if (null === $compat || 'ready' === ($compat['status'] ?? '')) {
                continue;
            }

            $definition = EngineCatalogDefinitions::findById($engineId);
            $req = EngineRequirementMatrix::findByEngineId($engineId);
            $needs = [];
            if (null !== $req?->requiredGpuVendor) {
                $needs[] = $req->requiredGpuVendor . ' GPU';
            }
            if ($req?->cudaRequired) {
                $needs[] = 'CUDA';
            }
            if (null !== $req?->minimumVramGb) {
                $needs[] = sprintf('%.0f GB VRAM', $req->minimumVramGb);
            }

            $items[] = [
                'engineId' => $engineId,
                'displayName' => $definition?->displayName ?? $engineId,
                'status' => $compat['status'] ?? 'blocked',
                'humanReason' => $compat['humanReason'] ?? 'Hardware requirement not met',
                'needs' => $needs,
                'recommendedAlternative' => $compat['recommendedAlternative'] ?? null,
            ];
        }

        return $items;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildTimeline(): array
    {
        $events = [];

        foreach ($this->benchmarkRunner->history() as $row) {
            if (!is_array($row) || !isset($row['at'], $row['engineId'])) {
                continue;
            }
            $events[] = [
                'at' => $row['at'],
                'type' => 'benchmark',
                'label' => 'Benchmark',
                'detail' => sprintf(
                    '%s — %s',
                    $row['engineId'],
                    ($row['ok'] ?? false) ? 'PASS' : 'FAIL',
                ),
            ];
        }

        foreach ($this->runtimeRepository->listValidationReports() as $report) {
            $events[] = [
                'at' => $report['validatedAt'] ?? '',
                'type' => 'validation',
                'label' => 'Validation',
                'detail' => sprintf('Pipeline %s — %s', $report['status'] ?? '?', $report['pipelineId'] ?? ''),
            ];
        }

        foreach ($this->runtimeRepository->listExecutions() as $execution) {
            $events[] = [
                'at' => $execution->completedAt ?? $execution->startedAt ?? '',
                'type' => 'execution',
                'label' => 'Engine run',
                'detail' => $execution->engineId,
            ];
        }

        foreach ($this->readyEngineInstallHints() as $hint) {
            $events[] = $hint;
        }

        usort($events, static fn (array $a, array $b): int => strcmp((string) $b['at'], (string) $a['at']));

        return array_slice($events, 0, 30);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function readyEngineInstallHints(): array
    {
        $readiness = $this->platform->readiness();
        $engines = is_array($readiness['engines'] ?? null) ? $readiness['engines'] : [];
        $hints = [];

        foreach ($engines as $engine) {
            if (!is_array($engine) || 'ready' !== ($engine['status'] ?? '')) {
                continue;
            }
            $spec = EngineProvisioningCatalog::find((string) $engine['id']);
            if (null === $spec || !$spec->autoProvisionSupported) {
                continue;
            }
            $hints[] = [
                'at' => (new \DateTimeImmutable())->format(DATE_ATOM),
                'type' => 'installed',
                'label' => 'Installed',
                'detail' => (string) ($engine['displayName'] ?? $engine['id']),
            ];
        }

        return $hints;
    }

    /**
     * @param array<string, array<string, mixed>> $compatById
     *
     * @return list<array<string, mixed>>
     */
    private function buildWarnings(array $compatById): array
    {
        $warnings = [];
        foreach ($compatById as $engineId => $compat) {
            if (!in_array($compat['status'] ?? '', ['blocked', 'missing', 'misconfigured'], true)) {
                continue;
            }
            if (($compat['canBeFixedByHardware'] ?? false) && !($compat['canBeFixedByInstall'] ?? false)) {
                continue;
            }
            $warnings[] = [
                'engineId' => $engineId,
                'severity' => $compat['severity'] ?? 'blocking',
                'humanReason' => $compat['humanReason'] ?? 'Engine not ready',
                'recommendedAlternative' => $compat['recommendedAlternative'] ?? null,
            ];
        }

        return array_slice($warnings, 0, 12);
    }

    /**
     * @param list<string> $compatibleReady
     * @param list<array<string, mixed>> $premiumFeatures
     *
     * @return array<string, mixed>
     */
    private function buildPipelineRecommendations(array $hardware, array $compatibleReady, array $premiumFeatures): array
    {
        $pipeline = is_array($hardware['recommendedPipeline'] ?? null) ? $hardware['recommendedPipeline'] : [];
        $lines = [];
        foreach ($pipeline as $stage => $engineId) {
            $lines[] = [
                'stage' => $stage,
                'engineId' => $engineId,
                'installed' => in_array($engineId, $compatibleReady, true),
            ];
        }

        return [
            'summary' => [] === $premiumFeatures
                ? 'Everything compatible with your hardware is installed.'
                : 'Premium features remain unavailable because of hardware limitations.',
            'pipeline' => $lines,
        ];
    }

    /**
     * @param array<string, mixed> $compatibility
     * @param array<string, mixed> $hardware
     * @param list<array<string, mixed>> $premiumFeatures
     *
     * @return array<string, mixed>
     */
    private function buildShadowCommentary(
        float $runtimeScore,
        array $compatibility,
        array $hardware,
        array $premiumFeatures,
        array $platformHealth,
    ): array {
        $profileLabel = $hardware['profile']['label'] ?? 'this machine';
        $coreHealth = is_array($platformHealth['coreHealth'] ?? null) ? $platformHealth['coreHealth'] : [];
        $coreReady = 'ready' === ($coreHealth['status'] ?? 'fail');

        $paragraphs = [];
        if ($coreReady) {
            $paragraphs[] = 'Runtime Core is healthy — all required capabilities are operational.';
            $paragraphs[] = 'Optional and premium capabilities are tracked separately and do not reduce core health.';
        } else {
            $paragraphs[] = 'Runtime Core needs attention on required capabilities.';
        }

        $optionalCaps = array_filter(
            is_array($platformHealth['capabilities'] ?? null) ? $platformHealth['capabilities'] : [],
            static fn (array $cap): bool => ($cap['classification'] ?? '') === 'optional',
        );
        if ([] !== $optionalCaps) {
            $paragraphs[] = 'OCR, Vision, Embeddings and Reranking are optional — not installed by default.';
        }

        if ([] !== $premiumFeatures) {
            $paragraphs[] = 'Premium engines remain visible when blocked by hardware. They become available with compatible GPU hardware without penalizing core health.';
        }

        $paragraphs[] = sprintf('Hardware profile: %s.', $profileLabel);

        return [
            'speaker' => 'Shadow',
            'message' => implode("\n\n", $paragraphs),
            'paragraphs' => $paragraphs,
        ];
    }

    /**
     * @return array{at: ?string, status: ?string, relative: ?string}|null
     */
    private function lastValidationSummary(): ?array
    {
        $reports = $this->runtimeRepository->listValidationReports();
        if ([] === $reports) {
            return null;
        }

        $latest = $reports[0];
        $at = $latest['validatedAt'] ?? null;
        $relative = null;
        if (is_string($at)) {
            try {
                $diff = (new \DateTimeImmutable())->getTimestamp() - (new \DateTimeImmutable($at))->getTimestamp();
                $relative = $diff < 120 ? 'just now' : sprintf('%d min ago', (int) round($diff / 60));
            } catch (\Exception) {
                $relative = null;
            }
        }

        return [
            'at' => $at,
            'status' => $latest['status'] ?? null,
            'relative' => $relative,
        ];
    }

    /**
     * @param array<string, mixed> $platformReadiness
     */
    private function shadowScore(array $platformReadiness): float
    {
        $checks = is_array($platformReadiness['checks'] ?? null) ? $platformReadiness['checks'] : [];
        $shadowOk = ($checks['shadowPersistence']['ok'] ?? false) && ($checks['learningPersistence']['ok'] ?? false);

        return $shadowOk ? 100.0 : 70.0;
    }

    /**
     * @param array<string, mixed> $platformReadiness
     */
    private function checkScore(array $platformReadiness, string $key): float
    {
        $checks = is_array($platformReadiness['checks'] ?? null) ? $platformReadiness['checks'] : [];

        return ($checks[$key]['ok'] ?? false) ? 100.0 : 0.0;
    }

    private function pipelineKeyFor(EngineCatalogCapability $capability): string
    {
        return match ($capability) {
            EngineCatalogCapability::SpeechToText => 'speech',
            EngineCatalogCapability::Translation => 'translation',
            EngineCatalogCapability::TextToSpeech => 'tts',
            EngineCatalogCapability::VoiceClone => 'voiceClone',
            EngineCatalogCapability::LipSync => 'lipSync',
            EngineCatalogCapability::VideoRender => 'render',
            default => $capability->value,
        };
    }

    private function pipelineKeyFromCapability(string $capability): string
    {
        return match ($capability) {
            'speech_to_text' => 'speech',
            'translation' => 'translation',
            'text_to_speech' => 'tts',
            'voice_clone' => 'voiceClone',
            'lip_sync' => 'lipSync',
            'video_render' => 'render',
            default => $capability,
        };
    }
}
