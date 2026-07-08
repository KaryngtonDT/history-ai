<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Lifecycle;

use App\Infrastructure\Runtime\Benchmark\BenchmarkRunner;

final class RuntimeEngineLifecycleService
{
    public function __construct(
        private readonly RuntimeProvisionManager $provisionManager,
        private readonly RuntimeUpdateManager $updateManager,
        private readonly RuntimeRepairManager $repairManager,
        private readonly RuntimeRemovalManager $removalManager,
        private readonly RuntimeVersionManager $versionManager,
        private readonly RuntimeDependencyManager $dependencyManager,
        private readonly RuntimeModelManager $modelManager,
        private readonly BenchmarkRunner $benchmarkRunner,
        private readonly RuntimeNotificationService $notificationService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function install(string $engineId): array
    {
        $result = $this->provisionManager->install($engineId);
        $this->notificationService->record(
            ($result['ok'] ?? false) ? 'engine_installed' : 'engine_validation_failed',
            $engineId,
            ($result['ok'] ?? false)
                ? sprintf('Engine %s installed successfully.', $engineId)
                : sprintf('Engine %s installation failed.', $engineId),
            $result,
        );

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function update(string $engineId): array
    {
        $result = $this->updateManager->update($engineId);
        $this->notificationService->record(
            'engine_updated',
            $engineId,
            sprintf('Engine %s update completed.', $engineId),
            $result,
        );

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function repair(string $engineId): array
    {
        return $this->repairManager->repair($engineId);
    }

    /**
     * @return array<string, mixed>
     */
    public function remove(string $engineId): array
    {
        return $this->removalManager->remove($engineId);
    }

    /**
     * @return array<string, mixed>
     */
    public function enable(string $engineId): array
    {
        return $this->removalManager->enable($engineId);
    }

    /**
     * @return array<string, mixed>
     */
    public function validate(string $engineId): array
    {
        $result = $this->benchmarkRunner->runEngine($engineId);
        $this->notificationService->record(
            ($result['ok'] ?? false) ? 'engine_validated' : 'engine_validation_failed',
            $engineId,
            ($result['ok'] ?? false)
                ? sprintf('Engine %s validation passed.', $engineId)
                : sprintf('Engine %s validation failed.', $engineId),
            $result,
        );

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function benchmark(string $engineId): array
    {
        $result = $this->benchmarkRunner->runEngine($engineId);
        $this->notificationService->record(
            'engine_benchmark_finished',
            $engineId,
            sprintf('Benchmark finished for %s.', $engineId),
            $result,
        );

        return $result;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function metadata(string $engineId): ?array
    {
        return [
            'engineId' => $engineId,
            'version' => $this->versionManager->version($engineId),
            'dependencies' => $this->dependencyManager->dependencies($engineId),
            'model' => $this->modelManager->modelInfo($engineId),
            'provision' => $this->provisionManager->spec($engineId),
        ];
    }
}
