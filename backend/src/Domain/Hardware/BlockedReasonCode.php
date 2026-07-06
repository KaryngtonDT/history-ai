<?php

declare(strict_types=1);

namespace App\Domain\Hardware;

enum BlockedReasonCode: string
{
    case None = 'none';
    case NvidiaCudaRequired = 'nvidia_cuda_required';
    case GpuNotFound = 'gpu_not_found';
    case VramInsufficient = 'vram_insufficient';
    case RamInsufficient = 'ram_insufficient';
    case ModelMissing = 'model_missing';
    case BinaryMissing = 'binary_missing';
    case PythonEnvMissing = 'python_env_missing';
    case DockerGpuNotAvailable = 'docker_gpu_not_available';
    case UnsupportedOnCurrentProvider = 'unsupported_on_current_provider';
    case OptionalLanguagePackMissing = 'optional_language_pack_missing';
    case NvencRequiresNvidia = 'nvenc_requires_nvidia';
    case NotInstalled = 'not_installed';

    public function label(): string
    {
        return match ($this) {
            self::None => 'None',
            self::NvidiaCudaRequired => 'NVIDIA CUDA required',
            self::GpuNotFound => 'GPU not found',
            self::VramInsufficient => 'Insufficient VRAM',
            self::RamInsufficient => 'Insufficient RAM',
            self::ModelMissing => 'Model files missing',
            self::BinaryMissing => 'Binary not found',
            self::PythonEnvMissing => 'Python environment missing',
            self::DockerGpuNotAvailable => 'Docker GPU access unavailable',
            self::UnsupportedOnCurrentProvider => 'Unsupported on current provider',
            self::OptionalLanguagePackMissing => 'Optional language pack missing',
            self::NvencRequiresNvidia => 'NVENC requires NVIDIA GPU',
            self::NotInstalled => 'Not installed',
        };
    }
}
