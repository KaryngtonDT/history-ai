<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Lifecycle;

use App\Domain\Engine\EngineRepositoryInterface;
use App\Infrastructure\Runtime\Catalog\EngineCatalogDefinitions;
use App\Infrastructure\Runtime\Provisioning\EngineProvisioningCatalog;

final class RuntimeModelManager
{
    public function __construct(
        private readonly EngineRepositoryInterface $engineRepository,
        private readonly string $modelsRoot,
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function modelInfo(string $engineId): ?array
    {
        $engine = $this->engineRepository->findById($engineId);
        $definition = EngineCatalogDefinitions::findById($engineId);
        $spec = EngineProvisioningCatalog::find($engineId);

        if (null === $engine && null === $definition) {
            return null;
        }

        $modelPath = $engine?->modelPath ?? $definition?->modelPath ?? $spec?->modelPath;

        return [
            'engineId' => $engineId,
            'modelPath' => $modelPath,
            'absoluteModelPath' => null !== $modelPath ? rtrim($this->modelsRoot, '/').'/'.$modelPath : null,
            'expectedModel' => $engine?->expectedModel ?? $definition?->expectedModel,
            'modelFound' => $engine?->modelFound ?? false,
            'modelDownloadHint' => $spec?->modelDownloadHint,
            'usesHuggingFaceCache' => $definition?->usesHuggingFaceCache ?? false,
        ];
    }
}
