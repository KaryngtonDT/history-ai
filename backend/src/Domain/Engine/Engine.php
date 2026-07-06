<?php

declare(strict_types=1);

namespace App\Domain\Engine;

use App\Domain\Runtime\EngineExecutionMode;
use App\Domain\Runtime\RuntimeStatus;

final readonly class Engine
{
    /**
     * @param list<EngineRequirement> $requirements
     */
    public function __construct(
        public string $id,
        public string $displayName,
        public EngineCatalogCapability $capability,
        public EngineFamily $family,
        public EngineCatalogRole $role,
        public bool $installed,
        public bool $compatible,
        public EngineCatalogTier $tier = EngineCatalogTier::Default,
        public ?EngineVersion $version = null,
        public ?string $binaryName = null,
        public ?string $modelPath = null,
        public array $requirements = [],
        public ?string $documentationUrl = null,
        public EngineExecutionMode $executionMode = EngineExecutionMode::Real,
        public RuntimeStatus $runtimeStatus = RuntimeStatus::Unknown,
        public bool $executableFound = false,
        public bool $modelFound = false,
        public bool $configured = false,
        public ?string $errorReason = null,
        public ?string $expectedModel = null,
        public ?string $ollamaModelTag = null,
        public ?string $installCommand = null,
        public ?string $modelDownloadHint = null,
        public ?string $documentationPath = null,
        public bool $autoProvisionSupported = false,
    ) {
    }

    public function isReady(): bool
    {
        return RuntimeStatus::Ready === $this->runtimeStatus;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'displayName' => $this->displayName,
            'capability' => $this->capability->value,
            'family' => $this->family->value,
            'role' => $this->role->value,
            'roleLabel' => $this->role->label(),
            'tier' => $this->tier->value,
            'tierLabel' => $this->tier->label(),
            'installed' => $this->installed,
            'compatible' => $this->compatible,
            'status' => $this->runtimeStatus->value,
            'mode' => $this->executionMode->value,
            'executableFound' => $this->executableFound,
            'modelFound' => $this->modelFound,
            'configured' => $this->configured,
            'errorReason' => $this->errorReason,
            'expectedModel' => $this->expectedModel,
            'ollamaModelTag' => $this->ollamaModelTag,
            'version' => $this->version?->toArray(),
            'binaryName' => $this->binaryName,
            'modelPath' => $this->modelPath,
            'requirements' => array_map(
                static fn (EngineRequirement $requirement): array => $requirement->toArray(),
                $this->requirements,
            ),
            'documentationUrl' => $this->documentationUrl,
            'installCommand' => $this->installCommand,
            'modelDownloadHint' => $this->modelDownloadHint,
            'documentationPath' => $this->documentationPath,
            'autoProvisionSupported' => $this->autoProvisionSupported,
            'runtimeReady' => $this->isReady(),
        ];
    }
}
