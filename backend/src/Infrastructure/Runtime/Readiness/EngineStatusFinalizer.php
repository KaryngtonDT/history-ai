<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Readiness;

use App\Domain\Engine\Engine;
use App\Domain\Runtime\RuntimeStatus;
use App\Infrastructure\Runtime\Provisioning\EngineProvisioningCatalog;

final class EngineStatusFinalizer
{
    public function finalize(Engine $engine): Engine
    {
        if ($engine->isReady()) {
            return $engine;
        }

        $spec = EngineProvisioningCatalog::find($engine->id);

        if (null !== $spec && !$spec->autoProvisionSupported) {
            $blockedReason = $spec->blockedReason ?? $engine->errorReason ?? 'Manual installation required.';
        } else {
            $blockedReason = $engine->errorReason ?? $spec?->blockedReason ?? 'Engine is not ready.';
        }

        $installCommand = $spec?->installCommand;
        $modelDownloadHint = $spec?->modelDownloadHint;
        $documentationPath = $spec?->documentationPath;
        $autoProvisionSupported = $spec?->autoProvisionSupported ?? false;

        return new Engine(
            id: $engine->id,
            displayName: $engine->displayName,
            capability: $engine->capability,
            family: $engine->family,
            role: $engine->role,
            installed: false,
            compatible: $engine->compatible,
            version: $engine->version,
            binaryName: $engine->binaryName,
            modelPath: $engine->modelPath,
            requirements: $engine->requirements,
            documentationUrl: $engine->documentationUrl,
            executionMode: $engine->executionMode,
            runtimeStatus: RuntimeStatus::Blocked,
            executableFound: $engine->executableFound,
            modelFound: $engine->modelFound,
            configured: $engine->configured,
            errorReason: $blockedReason,
            expectedModel: $engine->expectedModel,
            ollamaModelTag: $engine->ollamaModelTag,
            installCommand: $installCommand,
            modelDownloadHint: $modelDownloadHint,
            documentationPath: $documentationPath,
            autoProvisionSupported: $autoProvisionSupported,
        );
    }
}
