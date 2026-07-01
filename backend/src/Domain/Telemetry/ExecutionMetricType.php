<?php

declare(strict_types=1);

namespace App\Domain\Telemetry;

enum ExecutionMetricType: string
{
    case ProcessingTime = 'processing_time';
    case QueueTime = 'queue_time';
    case CpuUsage = 'cpu_usage';
    case GpuUsage = 'gpu_usage';
    case MemoryUsage = 'memory_usage';
    case SuccessRate = 'success_rate';
    case RetryCount = 'retry_count';

    public function defaultUnit(): string
    {
        return match ($this) {
            self::ProcessingTime, self::QueueTime => 'seconds',
            self::CpuUsage, self::GpuUsage, self::MemoryUsage, self::SuccessRate => 'percent',
            self::RetryCount => 'count',
        };
    }
}
