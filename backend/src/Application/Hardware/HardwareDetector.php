<?php

declare(strict_types=1);

namespace App\Application\Hardware;

use App\Domain\Hardware\HardwareCapability;
use App\Domain\Hardware\HardwareDetectionReport;

final class HardwareDetector
{
    public function __construct(
        private readonly GpuDetector $gpuDetector,
        private readonly CudaDetector $cudaDetector,
        private readonly RocmDetector $rocmDetector,
        private readonly DirectMlDetector $directMlDetector,
        private readonly RamDetector $ramDetector,
        private readonly DockerResourceDetector $dockerResourceDetector,
        private readonly WslDetector $wslDetector,
        private readonly HardwareReportBuilder $reportBuilder,
    ) {
    }

    public function detect(): HardwareDetectionReport
    {
        return $this->reportBuilder->build($this->detectCapabilities());
    }

    public function detectCapabilities(): HardwareCapability
    {
        $gpu = $this->gpuDetector->detect();
        $ram = $this->ramDetector->detect();
        $docker = $this->dockerResourceDetector->detect();

        return new HardwareCapability(
            cpuModel: $ram['cpuModel'],
            ramTotalGb: $ram['totalGb'],
            ramAvailableGb: $ram['availableGb'],
            gpuVendor: $gpu['vendor'],
            gpuName: $gpu['name'],
            vramGb: $gpu['vramGb'],
            cudaAvailable: $this->cudaDetector->isAvailable(),
            rocmAvailable: $this->rocmDetector->isAvailable(),
            directMlAvailable: $this->directMlDetector->isAvailable(),
            dockerGpuAccess: $docker['gpuAccess'],
            wsl2: $this->wslDetector->isWsl2(),
            dockerMemoryLimitGb: $docker['memoryLimitGb'],
            os: PHP_OS_FAMILY,
            pythonVersion: $this->pythonVersion(),
            ffmpegAvailable: $this->binaryExists('ffmpeg'),
            ollamaAvailable: $this->binaryExists('ollama'),
            diskFreeGb: $this->diskFreeGb(),
        );
    }

    private function pythonVersion(): ?string
    {
        $output = shell_exec('python3 --version 2>&1') ?? shell_exec('python --version 2>&1');

        return is_string($output) ? trim($output) : null;
    }

    private function binaryExists(string $binary): bool
    {
        $output = shell_exec('command -v '.escapeshellarg($binary).' 2>/dev/null');

        return is_string($output) && '' !== trim($output);
    }

    private function diskFreeGb(): ?float
    {
        $free = @disk_free_space('/');
        if (false === $free) {
            $free = @disk_free_space('.');
        }

        return false !== $free ? round($free / 1024 / 1024 / 1024, 1) : null;
    }
}
