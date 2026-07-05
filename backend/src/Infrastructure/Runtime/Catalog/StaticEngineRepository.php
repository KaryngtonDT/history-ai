<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Catalog;

use App\Domain\Engine\Engine;
use App\Domain\Engine\EngineCatalogCapability;
use App\Domain\Engine\EngineRepositoryInterface;
use App\Domain\Engine\EngineRequirement;
use App\Domain\Engine\EngineVersion;
use App\Infrastructure\Runtime\Discovery\BinaryScanner;
use App\Infrastructure\Runtime\Discovery\ModelScanner;
use App\Infrastructure\Runtime\Discovery\OllamaScanner;

final class StaticEngineRepository implements EngineRepositoryInterface
{
    public function __construct(
        private readonly BinaryScanner $binaryScanner,
        private readonly ModelScanner $modelScanner,
        private readonly OllamaScanner $ollamaScanner,
        private readonly string $ollamaModel,
    ) {
    }

    public function all(): array
    {
        return array_map(
            fn (EngineDefinition $definition): Engine => $this->buildEngine($definition),
            EngineCatalogDefinitions::all(),
        );
    }

    public function findById(string $id): ?Engine
    {
        foreach (EngineCatalogDefinitions::all() as $definition) {
            if ($definition->id === $id) {
                return $this->buildEngine($definition);
            }
        }

        return null;
    }

    public function findByCapability(EngineCatalogCapability $capability): array
    {
        return array_values(array_filter(
            $this->all(),
            static fn (Engine $engine): bool => $engine->capability === $capability,
        ));
    }

    private function buildEngine(EngineDefinition $definition): Engine
    {
        $requirements = [];
        $installed = false;
        $binaryPath = null;

        if ('ollama' === $definition->id) {
            $ollamaReady = $this->ollamaScanner->isAvailable();
            $modelReady = $this->ollamaScanner->hasModel($this->ollamaModel);
            $requirements[] = new EngineRequirement('ollama_api', 'Ollama API', $ollamaReady);
            $requirements[] = new EngineRequirement('ollama_model', 'Model '.$this->ollamaModel, $modelReady);
            $installed = $ollamaReady && $modelReady;
        } elseif (null !== $definition->binaryName) {
            $binaryPath = $this->binaryScanner->locate($definition->binaryName);
            $requirements[] = new EngineRequirement('binary', $definition->binaryName, null !== $binaryPath, $binaryPath);
            $installed = null !== $binaryPath;

            if (null !== $definition->modelPath) {
                $modelReady = $this->modelScanner->directoryExists($definition->modelPath);
                $requirements[] = new EngineRequirement(
                    'models',
                    'Models at '.$definition->modelPath,
                    $modelReady,
                    $this->modelScanner->resolvePath($definition->modelPath),
                );
                $installed = $installed && $modelReady;
            }
        }

        return new Engine(
            id: $definition->id,
            displayName: $definition->displayName,
            capability: $definition->capability,
            family: $definition->family,
            installed: $installed,
            compatible: true,
            version: null !== $binaryPath ? new EngineVersion('detected', $binaryPath) : null,
            binaryName: $definition->binaryName,
            modelPath: null !== $definition->modelPath ? $this->modelScanner->resolvePath($definition->modelPath) : null,
            requirements: $requirements,
            documentationUrl: $definition->documentationUrl,
        );
    }
}
