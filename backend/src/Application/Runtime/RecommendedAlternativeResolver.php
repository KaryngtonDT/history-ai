<?php

declare(strict_types=1);

namespace App\Application\Runtime;

use App\Domain\Hardware\HardwareCapability;
use App\Domain\Hardware\HardwareRequirement;
use App\Infrastructure\Runtime\Catalog\EngineCatalogDefinitions;
use App\Infrastructure\Runtime\Catalog\EngineRequirementMatrix;

final class RecommendedAlternativeResolver
{
    /**
     * @var array<string, string>
     */
    private const CAPABILITY_FALLBACKS = [
        'latentsync' => 'wav2lip',
        'echomimic_v2' => 'wav2lip',
        'musetalk' => 'wav2lip',
        'ffmpeg_nvenc' => 'ffmpeg_av1',
        'parakeet' => 'faster_whisper_large_v3',
        'canary' => 'faster_whisper_large_v3',
        'dia' => 'f5_tts',
    ];

    public function resolve(string $engineId, HardwareCapability $capabilities, bool $hardwareBlocked): ?string
    {
        if (isset(self::CAPABILITY_FALLBACKS[$engineId]) && $hardwareBlocked) {
            return self::CAPABILITY_FALLBACKS[$engineId];
        }

        $definition = EngineCatalogDefinitions::findById($engineId);
        if (null === $definition) {
            return null;
        }

        foreach (EngineCatalogDefinitions::all() as $candidate) {
            if ($candidate->capability !== $definition->capability || $candidate->id === $engineId) {
                continue;
            }

            $req = EngineRequirementMatrix::findByEngineId($candidate->id);
            if (null === $req) {
                continue;
            }

            if ($this->isHardwareCompatible($req, $capabilities)) {
                return $candidate->id;
            }
        }

        return null;
    }

    private function isHardwareCompatible(HardwareRequirement $requirement, HardwareCapability $capabilities): bool
    {
        if ($requirement->cudaRequired && !$capabilities->cudaAvailable) {
            return false;
        }

        if (null !== $requirement->requiredGpuVendor && 'NVIDIA' === $requirement->requiredGpuVendor && !$capabilities->hasNvidiaGpu()) {
            return false;
        }

        if (null !== $requirement->minimumVramGb) {
            $vram = $capabilities->vramGb ?? 0.0;
            if ($vram < $requirement->minimumVramGb) {
                return false;
            }
        }

        if (null !== $requirement->minimumRamGb) {
            $ram = $capabilities->ramAvailableGb ?? $capabilities->ramTotalGb ?? 0.0;
            if ($ram < $requirement->minimumRamGb) {
                return false;
            }
        }

        return true;
    }
}
