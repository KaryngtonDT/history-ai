<?php

declare(strict_types=1);

namespace App\Infrastructure\Hardware;

use App\Domain\Hardware\HardwareCapability;
use App\Domain\Hardware\HardwareDetectionReport;
use App\Domain\Hardware\HardwareProfile;
use App\Domain\Hardware\HardwareProfileType;
use App\Infrastructure\Storage\JsonFileStore;

final class HardwareReportStore
{
    private const string FILE = 'hardware-report.json';

    public function __construct(private readonly JsonFileStore $store)
    {
    }

    public function has(): bool
    {
        $data = $this->store->read(self::FILE);

        return is_array($data) && isset($data['profile']['type']);
    }

    public function save(HardwareDetectionReport $report): void
    {
        $this->store->write(self::FILE, $report->toArray());
    }

    public function get(): ?HardwareDetectionReport
    {
        $data = $this->store->read(self::FILE);
        if (!is_array($data)) {
            return null;
        }

        return $this->fromArray($data);
    }

    public function require(): HardwareDetectionReport
    {
        $report = $this->get();
        if (null === $report) {
            throw new \RuntimeException(
                'No hardware capability report found. Run GET /api/runtime/hardware first.',
            );
        }

        return $report;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function fromArray(array $data): HardwareDetectionReport
    {
        /** @var array<string, mixed> $profileData */
        $profileData = is_array($data['profile'] ?? null) ? $data['profile'] : [];
        /** @var array<string, mixed> $capData */
        $capData = is_array($data['capabilities'] ?? null) ? $data['capabilities'] : [];

        $capabilities = new HardwareCapability(
            cpuModel: is_string($capData['cpuModel'] ?? null) ? $capData['cpuModel'] : null,
            ramTotalGb: isset($capData['ramTotalGb']) ? (float) $capData['ramTotalGb'] : null,
            ramAvailableGb: isset($capData['ramAvailableGb']) ? (float) $capData['ramAvailableGb'] : null,
            gpuVendor: is_string($capData['gpuVendor'] ?? null) ? $capData['gpuVendor'] : null,
            gpuName: is_string($capData['gpuName'] ?? null) ? $capData['gpuName'] : null,
            vramGb: isset($capData['vramGb']) ? (float) $capData['vramGb'] : null,
            cudaAvailable: (bool) ($capData['cudaAvailable'] ?? false),
            rocmAvailable: (bool) ($capData['rocmAvailable'] ?? false),
            directMlAvailable: (bool) ($capData['directMlAvailable'] ?? false),
            dockerGpuAccess: (bool) ($capData['dockerGpuAccess'] ?? false),
            wsl2: (bool) ($capData['wsl2'] ?? false),
            dockerMemoryLimitGb: isset($capData['dockerMemoryLimitGb']) ? (float) $capData['dockerMemoryLimitGb'] : null,
            os: is_string($capData['os'] ?? null) ? $capData['os'] : null,
            pythonVersion: is_string($capData['pythonVersion'] ?? null) ? $capData['pythonVersion'] : null,
            ffmpegAvailable: (bool) ($capData['ffmpegAvailable'] ?? false),
            ollamaAvailable: (bool) ($capData['ollamaAvailable'] ?? false),
            diskFreeGb: isset($capData['diskFreeGb']) ? (float) $capData['diskFreeGb'] : null,
        );

        $type = HardwareProfileType::tryFrom((string) ($profileData['type'] ?? ''))
            ?? HardwareProfileType::Unknown;

        $profile = new HardwareProfile(
            $type,
            $capabilities,
            is_string($profileData['summary'] ?? null) ? $profileData['summary'] : $type->label(),
        );

        $detectedAt = \DateTimeImmutable::createFromFormat(DATE_ATOM, (string) ($data['detectedAt'] ?? ''))
            ?: new \DateTimeImmutable();

        return new HardwareDetectionReport($profile, $capabilities, $detectedAt);
    }
}
