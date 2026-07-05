<?php

declare(strict_types=1);

namespace App\Domain\Engine;

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
        public bool $installed,
        public bool $compatible,
        public ?EngineVersion $version = null,
        public ?string $binaryName = null,
        public ?string $modelPath = null,
        public array $requirements = [],
        public ?string $documentationUrl = null,
    ) {
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
            'installed' => $this->installed,
            'compatible' => $this->compatible,
            'version' => $this->version?->toArray(),
            'binaryName' => $this->binaryName,
            'modelPath' => $this->modelPath,
            'requirements' => array_map(
                static fn (EngineRequirement $requirement): array => $requirement->toArray(),
                $this->requirements,
            ),
            'documentationUrl' => $this->documentationUrl,
        ];
    }
}
