<?php

declare(strict_types=1);

namespace App\Domain\Telemetry;

use App\Domain\Telemetry\Exception\InvalidPipelineTelemetryException;

final readonly class ExecutionMetric
{
    public function __construct(
        private ExecutionMetricType $type,
        private float $value,
        private string $unit,
    ) {
        if ($value < 0) {
            throw new InvalidPipelineTelemetryException(sprintf(
                'Metric "%s" value cannot be negative.',
                $type->value,
            ));
        }

        if ('' === trim($unit)) {
            throw new InvalidPipelineTelemetryException('Metric unit cannot be empty.');
        }
    }

    public static function of(ExecutionMetricType $type, float $value, ?string $unit = null): self
    {
        return new self($type, $value, $unit ?? $type->defaultUnit());
    }

    public function type(): ExecutionMetricType
    {
        return $this->type;
    }

    public function value(): float
    {
        return $this->value;
    }

    public function unit(): string
    {
        return $this->unit;
    }
}
