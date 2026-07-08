<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Management;

use App\Application\Runtime\RuntimeResolverInterface;
use App\Domain\Engine\EngineCatalogCapability;
use App\Domain\Runtime\RuntimeRepositoryInterface;
use App\Infrastructure\Runtime\Discovery\EngineDiscovery;
use App\Infrastructure\Runtime\Health\RuntimePlatformHealthService;
use App\Infrastructure\Runtime\Intelligence\RecommendationEngine;
use App\Infrastructure\Runtime\Readiness\ReadinessEngine;

final class RuntimeDoctorReportService
{
    public function __construct(
        private readonly ReadinessEngine $readinessEngine,
        private readonly EngineDiscovery $engineDiscovery,
        private readonly RuntimeResolverInterface $runtimeResolver,
        private readonly RecommendationEngine $recommendationEngine,
        private readonly RuntimeRepositoryInterface $runtimeRepository,
        private readonly RuntimePlatformHealthService $platformHealthService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function report(): array
    {
        $readiness = $this->readinessEngine->evaluate();
        $engines = $this->engineDiscovery->discover();
        $recommendations = $this->recommendationEngine->recommend(
            $this->runtimeRepository->getConfiguration(),
        );
        $platformHealth = $this->platformHealthService->evaluate();
        $coreHealth = is_array($platformHealth['coreHealth'] ?? null) ? $platformHealth['coreHealth'] : [];

        $installed = [];
        $missing = [];
        $blocked = [];
        $mock = [];

        foreach ($engines as $engine) {
            $entry = [
                'engineId' => $engine->id,
                'displayName' => $engine->displayName,
                'capability' => $engine->capability->value,
                'status' => $engine->runtimeStatus->value,
                'mode' => $engine->executionMode->value,
                'installed' => $engine->installed,
                'ready' => $engine->isReady(),
                'blockedReason' => $engine->errorReason,
            ];

            if ('mock' === $engine->executionMode->value) {
                $mock[] = $entry;
            } elseif ($engine->isReady()) {
                $installed[] = $entry;
            } elseif ($engine->installed) {
                $blocked[] = $entry;
            } else {
                $missing[] = $entry;
            }
        }

        $capabilities = [];
        foreach (EngineCatalogCapability::cases() as $capability) {
            $view = $this->runtimeResolver->capabilitySelectionView($capability);
            $classified = null;
            foreach ($platformHealth['capabilities'] ?? [] as $capState) {
                if (($capState['capability'] ?? '') === $capability->value) {
                    $classified = $capState;
                    break;
                }
            }

            $capabilities[] = [
                'capability' => $capability->value,
                'label' => $capability->label(),
                'classification' => $classified['classification'] ?? null,
                'classificationLabel' => $classified['classificationLabel'] ?? null,
                'availability' => $classified['availability'] ?? null,
                'availabilityLabel' => $classified['availabilityLabel'] ?? null,
                'reason' => $classified['reason'] ?? null,
                'currentEngineId' => $view['currentEngineId'] ?? null,
                'recommendedEngineId' => $view['recommendedEngineId'] ?? null,
                'executable' => $view['executable'] ?? false,
                'blocked' => $view['blocked'] ?? false,
                'blockedReason' => $view['blockedReason'] ?? null,
                'futureHardware' => $classified['futureHardware'] ?? null,
            ];
        }

        $coreReady = 'ready' === ($coreHealth['status'] ?? 'fail');

        return [
            'status' => $coreReady ? 'ready' : 'degraded',
            'coreStatus' => $coreReady ? 'ready' : 'fail',
            'readyCount' => $readiness->readyCount,
            'totalCount' => $readiness->totalCount,
            'platformHealth' => $platformHealth,
            'installed' => $installed,
            'missing' => $missing,
            'blocked' => $blocked,
            'mock' => $mock,
            'capabilities' => $capabilities,
            'recommendations' => $recommendations,
            'at' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ];
    }
}
