<?php

declare(strict_types=1);

namespace App\Domain\Hardware;

enum FixType: string
{
    case InstallModel = 'install_model';
    case InstallDependency = 'install_dependency';
    case UseCompatibleAlternative = 'use_compatible_alternative';
    case UseRemoteGpuProvider = 'use_remote_gpu_provider';
    case UpgradeHardware = 'upgrade_hardware';
    case None = 'none';

    public function label(): string
    {
        return match ($this) {
            self::InstallModel => 'Install missing model',
            self::InstallDependency => 'Install dependency',
            self::UseCompatibleAlternative => 'Use compatible alternative',
            self::UseRemoteGpuProvider => 'Use remote GPU provider',
            self::UpgradeHardware => 'Upgrade hardware',
            self::None => 'None',
        };
    }
}
