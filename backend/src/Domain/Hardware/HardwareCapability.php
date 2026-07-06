<?php

declare(strict_types=1);

namespace App\Domain\Hardware;

final readonly class HardwareCapability
{
    public function __construct(
        public ?string $cpuModel = null,
        public ?float $ramTotalGb = null,
        public ?float $ramAvailableGb = null,
        public ?string $gpuVendor = null,
        public ?string $gpuName = null,
        public ?float $vramGb = null,
        public bool $cudaAvailable = false,
        public bool $rocmAvailable = false,
        public bool $directMlAvailable = false,
        public bool $dockerGpuAccess = false,
        public bool $wsl2 = false,
        public ?float $dockerMemoryLimitGb = null,
        public ?string $os = null,
        public ?string $pythonVersion = null,
        public bool $ffmpegAvailable = false,
        public bool $ollamaAvailable = false,
        public ?float $diskFreeGb = null,
    ) {
    }

    public function hasNvidiaGpu(): bool
    {
        if ($this->cudaAvailable) {
            return true;
        }

        $vendor = strtolower((string) $this->gpuVendor);
        $name = strtolower((string) $this->gpuName);

        return str_contains($vendor, 'nvidia') || str_contains($name, 'nvidia') || str_contains($name, 'geforce') || str_contains($name, 'rtx') || str_contains($name, 'tesla');
    }

    public function hasDiscreteGpu(): bool
    {
        $name = strtolower((string) $this->gpuName);

        return null !== $this->gpuName
            && '' !== $this->gpuName
            && !str_contains($name, 'integrated')
            && !str_contains($name, 'vega')
            && !str_contains($name, 'radeon graphics');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'cpuModel' => $this->cpuModel,
            'ramTotalGb' => $this->ramTotalGb,
            'ramAvailableGb' => $this->ramAvailableGb,
            'gpuVendor' => $this->gpuVendor,
            'gpuName' => $this->gpuName,
            'vramGb' => $this->vramGb,
            'cudaAvailable' => $this->cudaAvailable,
            'rocmAvailable' => $this->rocmAvailable,
            'directMlAvailable' => $this->directMlAvailable,
            'dockerGpuAccess' => $this->dockerGpuAccess,
            'wsl2' => $this->wsl2,
            'dockerMemoryLimitGb' => $this->dockerMemoryLimitGb,
            'os' => $this->os,
            'pythonVersion' => $this->pythonVersion,
            'ffmpegAvailable' => $this->ffmpegAvailable,
            'ollamaAvailable' => $this->ollamaAvailable,
            'diskFreeGb' => $this->diskFreeGb,
        ];
    }
}
