<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime;

use App\Application\Runtime\RuntimePlatformInterface;
use App\Domain\Engine\EngineProfileName;
use App\Domain\Engine\SelectionMode;
use App\Domain\Runtime\RuntimeConfiguration;
use App\Domain\Runtime\RuntimeRepositoryInterface;
use App\Domain\Runtime\RuntimeStatus;
use App\Infrastructure\Runtime\Benchmark\BenchmarkRunner;
use App\Infrastructure\Runtime\Discovery\EngineDiscovery;
use App\Infrastructure\Runtime\Health\HealthMonitor;
use App\Infrastructure\Runtime\Intelligence\AutoSelectionEngine;
use App\Infrastructure\Runtime\Intelligence\RecommendationEngine;
use App\Infrastructure\Runtime\Readiness\ReadinessEngine;

final class RuntimePlatformService implements RuntimePlatformInterface
{
    public function __construct(
        private readonly ReadinessEngine $readinessEngine,
        private readonly HealthMonitor $healthMonitor,
        private readonly EngineDiscovery $engineDiscovery,
        private readonly RecommendationEngine $recommendationEngine,
        private readonly AutoSelectionEngine $autoSelectionEngine,
        private readonly BenchmarkRunner $benchmarkRunner,
        private readonly RuntimeRepositoryInterface $runtimeRepository,
    ) {
    }

    public function overview(): array
    {
        $readiness = $this->readinessEngine->evaluate();
        $health = $this->healthMonitor->heartbeat();
        $config = $this->runtimeRepository->getConfiguration();

        return [
            'principle' => 'Configured. Verified. Measured. Intelligent. Explainable.',
            'status' => $readiness->status->value,
            'health' => $health->toArray(),
            'configuration' => $config->toArray(),
            'environment' => $this->engineDiscovery->environment(),
        ];
    }

    public function readiness(): array
    {
        return $this->readinessEngine->evaluate()->toArray();
    }

    public function health(): array
    {
        $health = $this->healthMonitor->heartbeat();

        return [
            ...$health->toArray(),
            'failureHistory' => $this->healthMonitor->failureHistory(),
        ];
    }

    public function engines(): array
    {
        return array_map(
            static fn ($engine): array => $engine->toArray(),
            $this->engineDiscovery->discover(),
        );
    }

    public function catalog(): array
    {
        $engines = $this->engineDiscovery->discover();

        return [
            'installed' => array_values(array_map(
                static fn ($engine): array => $engine->toArray(),
                array_filter($engines, static fn ($engine): bool => $engine->installed),
            )),
            'available' => array_values(array_map(
                static fn ($engine): array => $engine->toArray(),
                array_filter($engines, static fn ($engine): bool => !$engine->installed && $engine->compatible),
            )),
            'compatible' => array_map(static fn ($e) => $e->toArray(), $engines),
        ];
    }

    public function recommendations(): array
    {
        return $this->recommendationEngine->recommend($this->runtimeRepository->getConfiguration());
    }

    public function profiles(): array
    {
        return EngineProfileName::catalog();
    }

    public function testEngine(string $engineId): array
    {
        return $this->benchmarkRunner->runEngine($engineId);
    }

    public function benchmark(?string $engineId = null): array
    {
        if (null !== $engineId && '' !== trim($engineId)) {
            return $this->benchmarkRunner->runEngine($engineId);
        }

        return $this->benchmarkRunner->runFull();
    }

    public function validatePipeline(): array
    {
        $pipelineId = bin2hex(random_bytes(16));
        $pipelineId = sprintf(
            '%s-%s-%s-%s-%s',
            substr($pipelineId, 0, 8),
            substr($pipelineId, 8, 4),
            substr($pipelineId, 12, 4),
            substr($pipelineId, 16, 4),
            substr($pipelineId, 20, 12),
        );
        $readiness = $this->readinessEngine->evaluate();
        $config = $this->runtimeRepository->getConfiguration();
        $selections = $this->autoSelectionEngine->resolveSelections($config);
        $steps = [];
        $passed = true;

        foreach ($readiness->engines as $engine) {
            if (!$engine->configured) {
                continue;
            }

            $stepOk = $engine->isReady();
            $passed = $passed && $stepOk;
            $steps[] = [
                'capability' => $engine->capability->value,
                'requestedEngineId' => $selections[$engine->capability->value] ?? $engine->id,
                'executedEngineId' => $engine->id,
                'status' => $engine->status->value,
                'mode' => $engine->mode->value,
                'executableFound' => $engine->executableFound,
                'modelFound' => $engine->modelFound,
                'fallbackUsed' => false,
                'reason' => $stepOk ? null : ($engine->errorReason ?? 'Engine not ready'),
                'confidence' => $stepOk ? 100 : 0,
            ];
        }

        $report = [
            'pipelineId' => $pipelineId,
            'status' => $passed ? 'pass' : 'fail',
            'steps' => $steps,
            'validatedAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ];

        $this->runtimeRepository->saveValidationReport($pipelineId, $report);

        return $report;
    }

    public function updateProfile(array $payload): array
    {
        $config = $this->runtimeRepository->getConfiguration();
        $profile = EngineProfileName::tryFrom((string) ($payload['profile'] ?? '')) ?? $config->profile;
        $updated = $config->withProfile($profile);
        $this->runtimeRepository->saveConfiguration($updated);

        return $updated->toArray();
    }

    public function updateSelection(array $payload): array
    {
        $config = $this->runtimeRepository->getConfiguration();
        $mode = SelectionMode::tryFrom((string) ($payload['selectionMode'] ?? '')) ?? $config->selectionMode;
        $manual = [];

        if (isset($payload['manualSelections']) && is_array($payload['manualSelections'])) {
            foreach ($payload['manualSelections'] as $capability => $engineId) {
                if (is_string($capability) && is_string($engineId)) {
                    $manual[$capability] = $engineId;
                }
            }
        }

        $updated = $config->withSelectionMode($mode)->withManualSelections($manual);
        $this->runtimeRepository->saveConfiguration($updated);

        return $updated->toArray();
    }

    public function report(string $pipelineId): ?array
    {
        return $this->runtimeRepository->findValidationReport($pipelineId);
    }
}
