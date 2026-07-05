<?php

declare(strict_types=1);

namespace App\Domain\Runtime;

final readonly class RuntimeEngine
{
    /**
     * @param list<RuntimeRequirement> $requirements
     */
    public function __construct(
        public string $id,
        public string $displayName,
        public RuntimeCapability $capability,
        public RuntimeStatus $status,
        public bool $configured,
        public bool $discovered,
        public ?string $version = null,
        public ?string $binaryPath = null,
        public array $requirements = [],
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
            'configured' => $this->configured,
            'discovered' => $this->discovered,
            'version' => $this->version,
            'binaryPath' => $this->binaryPath,
            'requirements' => array_map(
                static fn (RuntimeRequirement $requirement): array => $requirement->toArray(),
                $this->requirements,
            ),
        ];
    }
}
