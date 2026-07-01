<?php

declare(strict_types=1);

namespace App\Domain\Telemetry;

final readonly class ExecutionMetricCollection
{
    /** @var list<ExecutionMetric> */
    private array $metrics;

    /**
     * @param list<ExecutionMetric> $metrics
     */
    public function __construct(array $metrics = [])
    {
        $this->metrics = array_values($metrics);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<ExecutionMetric>
     */
    public function all(): array
    {
        return $this->metrics;
    }

    public function count(): int
    {
        return count($this->metrics);
    }

    public function isEmpty(): bool
    {
        return [] === $this->metrics;
    }

    public function append(ExecutionMetric $metric): self
    {
        return new self([...$this->metrics, $metric]);
    }

    public function findByType(ExecutionMetricType $type): ?ExecutionMetric
    {
        foreach ($this->metrics as $metric) {
            if ($metric->type() === $type) {
                return $metric;
            }
        }

        return null;
    }

    public function averageForType(ExecutionMetricType $type): ?float
    {
        $values = array_map(
            static fn (ExecutionMetric $metric): float => $metric->value(),
            array_filter(
                $this->metrics,
                static fn (ExecutionMetric $metric): bool => $metric->type() === $type,
            ),
        );

        if ([] === $values) {
            return null;
        }

        return array_sum($values) / count($values);
    }
}
