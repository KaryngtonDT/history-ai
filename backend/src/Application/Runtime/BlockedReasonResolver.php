<?php

declare(strict_types=1);

namespace App\Application\Runtime;

use App\Domain\Hardware\BlockedReasonCode;
use App\Domain\Hardware\HardwareCapability;
use App\Domain\Hardware\HardwareRequirement;
use App\Domain\Runtime\EngineExecutionMode;
use App\Domain\Runtime\RuntimeStatus;
use App\Infrastructure\Runtime\Catalog\EngineCatalogDefinitions;

final class BlockedReasonResolver
{
    /**
     * @param list<string> $missingRequirements
     */
    public function resolve(
        string $engineId,
        HardwareRequirement $requirement,
        HardwareCapability $capabilities,
        RuntimeStatus $runtimeStatus,
        bool $executableFound,
        bool $modelFound,
        array $missingRequirements,
    ): array {
        if ([] !== $missingRequirements) {
            $code = match (true) {
                in_array('NVENC', $missingRequirements, true) => BlockedReasonCode::NvencRequiresNvidia,
                in_array('NVIDIA GPU', $missingRequirements, true) || in_array('CUDA', $missingRequirements, true) => BlockedReasonCode::NvidiaCudaRequired,
                str_contains(implode(' ', $missingRequirements), 'VRAM') => BlockedReasonCode::VramInsufficient,
                str_contains(implode(' ', $missingRequirements), 'RAM') => BlockedReasonCode::RamInsufficient,
                default => BlockedReasonCode::GpuNotFound,
            };

            return [
                'code' => $code,
                'humanReason' => $this->hardwareHumanReason($engineId, $requirement, $capabilities, $missingRequirements),
                'severity' => 'blocking',
            ];
        }

        if (RuntimeStatus::Mock === $runtimeStatus) {
            return [
                'code' => BlockedReasonCode::None,
                'humanReason' => sprintf('%s is running in mock mode.', $this->displayName($engineId)),
                'severity' => 'warning',
            ];
        }

        if (!$executableFound) {
            return [
                'code' => BlockedReasonCode::BinaryMissing,
                'humanReason' => sprintf('%s binary is not installed or not on PATH.', $this->displayName($engineId)),
                'severity' => 'blocking',
            ];
        }

        if ($requirement->engineId !== 'ffmpeg' && !$modelFound && EngineCatalogDefinitions::findById($engineId)?->requiresModelFiles) {
            return [
                'code' => BlockedReasonCode::ModelMissing,
                'humanReason' => sprintf('%s model files are missing.', $this->displayName($engineId)),
                'severity' => 'blocking',
            ];
        }

        if (null === $capabilities->pythonVersion && $this->requiresPython($engineId)) {
            return [
                'code' => BlockedReasonCode::PythonEnvMissing,
                'humanReason' => sprintf('%s requires a Python runtime.', $this->displayName($engineId)),
                'severity' => 'blocking',
            ];
        }

        if ($requirement->cudaRequired && !$capabilities->dockerGpuAccess && file_exists('/.dockerenv')) {
            return [
                'code' => BlockedReasonCode::DockerGpuNotAvailable,
                'humanReason' => sprintf('%s requires GPU access inside Docker, but no NVIDIA GPU is exposed to the container.', $this->displayName($engineId)),
                'severity' => 'blocking',
            ];
        }

        return [
            'code' => BlockedReasonCode::NotInstalled,
            'humanReason' => sprintf('%s is not ready on this machine.', $this->displayName($engineId)),
            'severity' => 'blocking',
        ];
    }

    /**
     * @param list<string> $missingRequirements
     */
    private function hardwareHumanReason(
        string $engineId,
        HardwareRequirement $requirement,
        HardwareCapability $capabilities,
        array $missingRequirements,
    ): string {
        $displayName = $this->displayName($engineId);
        $gpu = $capabilities->gpuName ?? 'no GPU detected';
        $missing = implode(', ', $missingRequirements);

        if ($requirement->cudaRequired) {
            return sprintf(
                '%s requires NVIDIA CUDA%s. This machine has %s and no CUDA.',
                $displayName,
                null !== $requirement->minimumVramGb ? sprintf(' with around %.0f GB VRAM', $requirement->minimumVramGb) : '',
                $gpu,
            );
        }

        if ($requirement->nvencRequired) {
            return sprintf('%s requires NVIDIA NVENC hardware encoding. This machine has %s.', $displayName, $gpu);
        }

        return sprintf('%s is blocked by hardware requirements: %s.', $displayName, $missing);
    }

    private function displayName(string $engineId): string
    {
        return EngineCatalogDefinitions::findById($engineId)?->displayName ?? $engineId;
    }

    private function requiresPython(string $engineId): bool
    {
        $definition = EngineCatalogDefinitions::findById($engineId);
        if (null === $definition) {
            return false;
        }

        return EngineExecutionMode::Real === $definition->installedMode && null !== $definition->binaryName;
    }
}
