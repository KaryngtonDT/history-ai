<?php

declare(strict_types=1);

namespace App\Domain\Runtime;

final readonly class RuntimeEngine
{
    /**
     * @param list<RuntimeRequirement> $requirements
     * @param array<string, mixed>|null $lastTestResult
     */
    public function __construct(
        public string $id,
        public string $displayName,
        public RuntimeCapability $capability,
        public RuntimeStatus $status,
        public EngineExecutionMode $mode,
        public bool $configured,
        public bool $discovered,
        public bool $executableFound,
        public bool $modelFound,
        public ?string $role = null,
        public ?string $roleLabel = null,
        public ?string $tier = null,
        public ?string $tierLabel = null,
        public ?string $version = null,
        public ?string $binaryPath = null,
        public ?string $errorReason = null,
        public ?string $expectedModel = null,
        public array $requirements = [],
        public ?array $lastTestResult = null,
        public ?string $installCommand = null,
        public ?string $modelDownloadHint = null,
        public ?string $documentationPath = null,
        public bool $autoProvisionSupported = false,
    ) {
    }

    public function isReady(): bool
    {
        return RuntimeStatus::Ready === $this->status;
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
            'status' => $this->status->value,
            'mode' => $this->mode->value,
            'role' => $this->role,
            'roleLabel' => $this->roleLabel,
            'tier' => $this->tier,
            'tierLabel' => $this->tierLabel,
            'configured' => $this->configured,
            'discovered' => $this->discovered,
            'executableFound' => $this->executableFound,
            'modelFound' => $this->modelFound,
            'errorReason' => $this->errorReason,
            'expectedModel' => $this->expectedModel,
            'version' => $this->version,
            'binaryPath' => $this->binaryPath,
            'requirements' => array_map(
                static fn (RuntimeRequirement $requirement): array => $requirement->toArray(),
                $this->requirements,
            ),
            'lastTestResult' => $this->lastTestResult,
            'installCommand' => $this->installCommand,
            'modelDownloadHint' => $this->modelDownloadHint,
            'documentationPath' => $this->documentationPath,
            'autoProvisionSupported' => $this->autoProvisionSupported,
            'runtimeReady' => $this->isReady(),
        ];
    }
}
