<?php

declare(strict_types=1);

namespace App\Domain\Hardware;

final readonly class HardwareRequirement
{
    /**
     * @param list<string> $supportedProviders
     * @param list<string> $optionalLanguagePacks
     * @param list<string> $optionalFeatures
     */
    public function __construct(
        public string $engineId,
        public ?string $requiredGpuVendor = null,
        public bool $cudaRequired = false,
        public bool $cudaRecommended = false,
        public ?float $minimumVramGb = null,
        public ?float $minimumRamGb = null,
        public bool $cpuFallbackSupported = true,
        public array $supportedProviders = ['host', 'docker', 'remote'],
        public array $optionalLanguagePacks = [],
        public array $optionalFeatures = [],
        public bool $nvencRequired = false,
        public ?string $documentationLink = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'engineId' => $this->engineId,
            'requiredGpuVendor' => $this->requiredGpuVendor,
            'cudaRequired' => $this->cudaRequired,
            'cudaRecommended' => $this->cudaRecommended,
            'minimumVramGb' => $this->minimumVramGb,
            'minimumRamGb' => $this->minimumRamGb,
            'cpuFallbackSupported' => $this->cpuFallbackSupported,
            'supportedProviders' => $this->supportedProviders,
            'optionalLanguagePacks' => $this->optionalLanguagePacks,
            'optionalFeatures' => $this->optionalFeatures,
            'nvencRequired' => $this->nvencRequired,
            'documentationLink' => $this->documentationLink,
        ];
    }
}
