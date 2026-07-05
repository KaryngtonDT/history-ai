<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Discovery;

use App\Domain\Engine\Engine;
use App\Domain\Engine\EngineRepositoryInterface;
use App\Infrastructure\Runtime\Catalog\EngineCatalogDefinitions;

final class EngineDiscovery
{
    public function __construct(
        private readonly EngineRepositoryInterface $engineRepository,
        private readonly PythonScanner $pythonScanner,
        private readonly CudaScanner $cudaScanner,
        private readonly EnvironmentScanner $environmentScanner,
        private readonly string $sttProvider,
        private readonly string $translationProvider,
        private readonly string $ttsProvider,
        private readonly string $voiceCloneProvider,
        private readonly string $lipSyncProvider,
        private readonly string $videoRenderProvider,
    ) {
    }

    /**
     * @return list<Engine>
     */
    public function discover(): array
    {
        return $this->engineRepository->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function environment(): array
    {
        return [
            'python' => [
                'available' => $this->pythonScanner->isAvailable(),
                'version' => $this->pythonScanner->version(),
            ],
            'cuda' => [
                'available' => $this->cudaScanner->isAvailable(),
                'device' => $this->cudaScanner->deviceName(),
            ],
            'activeProviders' => $this->environmentScanner->activeProviders(
                $this->sttProvider,
                $this->translationProvider,
                $this->ttsProvider,
                $this->voiceCloneProvider,
                $this->lipSyncProvider,
                $this->videoRenderProvider,
            ),
        ];
    }

    public function findEngine(string $id): ?Engine
    {
        return $this->engineRepository->findById($id);
    }

    public function defaultEngineId(string $capability): ?string
    {
        foreach (EngineCatalogDefinitions::all() as $definition) {
            if ($definition->capability->value === $capability && $definition->default) {
                return $definition->id;
            }
        }

        return null;
    }
}
