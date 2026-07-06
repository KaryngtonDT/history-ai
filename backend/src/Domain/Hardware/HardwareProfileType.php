<?php

declare(strict_types=1);

namespace App\Domain\Hardware;

enum HardwareProfileType: string
{
    case CpuOnly = 'cpu_only';
    case LowEndLocal = 'low_end_local';
    case MidRangeNvidia = 'mid_range_nvidia';
    case HighEndNvidia = 'high_end_nvidia';
    case EnterpriseGpu = 'enterprise_gpu';
    case Unknown = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::CpuOnly => 'CPU Only',
            self::LowEndLocal => 'Low-End Local',
            self::MidRangeNvidia => 'Mid-Range NVIDIA',
            self::HighEndNvidia => 'High-End NVIDIA',
            self::EnterpriseGpu => 'Enterprise GPU',
            self::Unknown => 'Unknown',
        };
    }
}
