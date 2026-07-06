<?php

declare(strict_types=1);

namespace App\Application\Hardware;

use App\Domain\Hardware\HardwareCapability;
use App\Domain\Hardware\HardwareProfile;
use App\Domain\Hardware\HardwareProfileType;

final class HardwareProfileClassifier
{
    public function classify(HardwareCapability $capabilities): HardwareProfile
    {
        $type = $this->resolveType($capabilities);
        $summary = $this->buildSummary($type, $capabilities);

        return new HardwareProfile($type, $capabilities, $summary);
    }

    private function resolveType(HardwareCapability $capabilities): HardwareProfileType
    {
        if ($capabilities->cudaAvailable && $capabilities->hasNvidiaGpu()) {
            $vram = $capabilities->vramGb ?? 0.0;

            return match (true) {
                $vram >= 40 => HardwareProfileType::EnterpriseGpu,
                $vram >= 16 => HardwareProfileType::HighEndNvidia,
                $vram >= 8 => HardwareProfileType::MidRangeNvidia,
                default => HardwareProfileType::LowEndLocal,
            };
        }

        if (null === $capabilities->gpuName && !$capabilities->cudaAvailable) {
            return HardwareProfileType::CpuOnly;
        }

        if (!$capabilities->cudaAvailable) {
            return HardwareProfileType::LowEndLocal;
        }

        return HardwareProfileType::Unknown;
    }

    private function buildSummary(HardwareProfileType $type, HardwareCapability $capabilities): string
    {
        $gpu = $capabilities->gpuName ?? 'no discrete GPU detected';
        $ram = null !== $capabilities->ramTotalGb ? sprintf('%.1f GB RAM', $capabilities->ramTotalGb) : 'unknown RAM';

        return match ($type) {
            HardwareProfileType::CpuOnly => sprintf('CPU-only machine (%s, %s).', $gpu, $ram),
            HardwareProfileType::LowEndLocal => sprintf('Low-end local machine (%s, %s, no CUDA).', $gpu, $ram),
            HardwareProfileType::MidRangeNvidia => sprintf('Mid-range NVIDIA GPU (%s, %s).', $gpu, $ram),
            HardwareProfileType::HighEndNvidia => sprintf('High-end NVIDIA GPU (%s, %s).', $gpu, $ram),
            HardwareProfileType::EnterpriseGpu => sprintf('Enterprise NVIDIA GPU (%s, %s).', $gpu, $ram),
            HardwareProfileType::Unknown => sprintf('Hardware profile unknown (%s, %s).', $gpu, $ram),
        };
    }
}
