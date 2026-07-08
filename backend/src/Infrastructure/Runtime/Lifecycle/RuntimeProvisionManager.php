<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Lifecycle;

use App\Infrastructure\Runtime\Provisioning\EngineProvisioner;
use App\Infrastructure\Runtime\Provisioning\EngineProvisioningCatalog;

final class RuntimeProvisionManager
{
    public function __construct(private readonly EngineProvisioner $engineProvisioner)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function install(string $engineId): array
    {
        return $this->engineProvisioner->provision($engineId);
    }

    /**
     * @return array<string, mixed>
     */
    public function installAll(): array
    {
        return $this->engineProvisioner->provisionAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function spec(string $engineId): ?array
    {
        $spec = EngineProvisioningCatalog::find($engineId);
        if (null === $spec) {
            return null;
        }

        return [
            'engineId' => $spec->engineId,
            'autoProvisionSupported' => $spec->autoProvisionSupported,
            'blockedReason' => $spec->blockedReason,
            'installCommand' => $spec->installCommand,
            'modelDownloadHint' => $spec->modelDownloadHint,
            'modelPath' => $spec->modelPath,
            'documentationPath' => $spec->documentationPath,
        ];
    }
}
