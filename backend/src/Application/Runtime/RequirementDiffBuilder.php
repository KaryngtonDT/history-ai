<?php

declare(strict_types=1);

namespace App\Application\Runtime;

use App\Domain\Hardware\HardwareCapability;
use App\Domain\Hardware\HardwareRequirement;

final class RequirementDiffBuilder
{
    /**
     * @return list<string>
     */
    public function build(HardwareRequirement $requirement, HardwareCapability $capabilities): array
    {
        $missing = [];

        if (null !== $requirement->requiredGpuVendor && 'NVIDIA' === $requirement->requiredGpuVendor && !$capabilities->hasNvidiaGpu()) {
            $missing[] = 'NVIDIA GPU';
        }

        if ($requirement->cudaRequired && !$capabilities->cudaAvailable) {
            $missing[] = 'CUDA';
        }

        if ($requirement->nvencRequired && !$capabilities->hasNvidiaGpu()) {
            $missing[] = 'NVENC';
        }

        if (null !== $requirement->minimumVramGb) {
            $vram = $capabilities->vramGb ?? 0.0;
            if ($vram < $requirement->minimumVramGb) {
                $missing[] = sprintf('%.0f GB VRAM', $requirement->minimumVramGb);
            }
        }

        if (null !== $requirement->minimumRamGb) {
            $ram = $capabilities->ramAvailableGb ?? $capabilities->ramTotalGb ?? 0.0;
            if ($ram < $requirement->minimumRamGb) {
                $missing[] = sprintf('%.0f GB RAM', $requirement->minimumRamGb);
            }
        }

        if ($requirement->cudaRequired && file_exists('/.dockerenv') && !$capabilities->dockerGpuAccess) {
            $missing[] = 'Docker GPU access';
        }

        return $missing;
    }
}
