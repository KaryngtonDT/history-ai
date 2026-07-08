<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Lifecycle;

use App\Infrastructure\Runtime\Provisioning\EngineProvisioningCatalog;

final class RuntimeDependencyManager
{
    /**
     * @return array<string, mixed>|null
     */
    public function dependencies(string $engineId): ?array
    {
        $spec = EngineProvisioningCatalog::find($engineId);
        if (null === $spec) {
            return null;
        }

        return [
            'engineId' => $engineId,
            'autoProvisionSupported' => $spec->autoProvisionSupported,
            'installCommand' => $spec->installCommand,
            'modelDownloadHint' => $spec->modelDownloadHint,
            'documentationPath' => $spec->documentationPath,
        ];
    }
}
