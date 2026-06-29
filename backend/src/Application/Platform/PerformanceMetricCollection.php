<?php

declare(strict_types=1);

namespace App\Application\Platform;

final readonly class PerformanceMetricCollection
{
    /** @var list<PerformanceMetric> */
    private array $metrics;

    /**
     * @param list<PerformanceMetric> $metrics
     */
    public function __construct(array $metrics = [])
    {
        $this->metrics = array_values($metrics);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function with(PerformanceMetric $metric): self
    {
        return new self([...$this->metrics, $metric]);
    }

    public function merge(self $other): self
    {
        return new self([...$this->metrics, ...$other->metrics]);
    }

    public function isEmpty(): bool
    {
        return [] === $this->metrics;
    }

    /**
     * @return list<PerformanceMetric>
     */
    public function metrics(): array
    {
        return $this->metrics;
    }

    /**
     * @return list<string>
     */
    public function names(): array
    {
        return array_map(
            static fn (PerformanceMetric $metric): string => $metric->name,
            $this->metrics,
        );
    }
}
