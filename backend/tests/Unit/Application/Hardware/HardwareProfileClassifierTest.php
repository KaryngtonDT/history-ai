<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Hardware;

use App\Application\Hardware\HardwareProfileClassifier;
use App\Domain\Hardware\HardwareCapability;
use App\Domain\Hardware\HardwareProfileType;
use PHPUnit\Framework\TestCase;

final class HardwareProfileClassifierTest extends TestCase
{
    private HardwareProfileClassifier $classifier;

    protected function setUp(): void
    {
        $this->classifier = new HardwareProfileClassifier();
    }

    public function testClassifiesAmdIntegratedGpuAsLowEndLocal(): void
    {
        $capabilities = new HardwareCapability(
            cpuModel: 'AMD Ryzen 7',
            ramTotalGb: 16.0,
            ramAvailableGb: 4.5,
            gpuVendor: 'AMD',
            gpuName: 'AMD Radeon integrated graphics',
            vramGb: null,
            cudaAvailable: false,
        );

        $profile = $this->classifier->classify($capabilities);

        self::assertSame(HardwareProfileType::LowEndLocal, $profile->type);
        self::assertStringContainsString('no CUDA', $profile->summary);
    }

    public function testClassifiesHighEndNvidiaGpu(): void
    {
        $capabilities = new HardwareCapability(
            gpuVendor: 'NVIDIA',
            gpuName: 'NVIDIA GeForce RTX 4090',
            vramGb: 24.0,
            cudaAvailable: true,
            ramTotalGb: 64.0,
        );

        $profile = $this->classifier->classify($capabilities);

        self::assertSame(HardwareProfileType::HighEndNvidia, $profile->type);
    }

    public function testClassifiesCpuOnlyWhenNoGpuDetected(): void
    {
        $capabilities = new HardwareCapability(
            ramTotalGb: 8.0,
            cudaAvailable: false,
        );

        $profile = $this->classifier->classify($capabilities);

        self::assertSame(HardwareProfileType::CpuOnly, $profile->type);
    }
}
