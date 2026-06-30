<?php

declare(strict_types=1);

namespace App\Domain\Quality;

use App\Domain\Quality\Exception\InvalidQualityReportException;

final readonly class QualityMetricCollection
{
    /** @var list<QualityMetric> */
    private array $metrics;

    /**
     * @param list<QualityMetric> $metrics
     */
    public function __construct(array $metrics)
    {
        if ([] === $metrics) {
            throw new InvalidQualityReportException('Quality metrics cannot be empty.');
        }

        $seen = [];

        foreach ($metrics as $metric) {
            $key = $metric->category()->value;

            if (isset($seen[$key])) {
                throw new InvalidQualityReportException(sprintf(
                    'Duplicate quality metric "%s".',
                    $key,
                ));
            }

            $seen[$key] = true;
        }

        $this->metrics = array_values($metrics);
    }

    /**
     * @return list<QualityMetric>
     */
    public function all(): array
    {
        return $this->metrics;
    }

    public function count(): int
    {
        return count($this->metrics);
    }

    public function forCategory(QualityCategory $category): ?QualityMetric
    {
        foreach ($this->metrics as $metric) {
            if ($metric->category() === $category) {
                return $metric;
            }
        }

        return null;
    }

    public function averageScore(): QualityScore
    {
        $total = 0;

        foreach ($this->metrics as $metric) {
            $total += $metric->score()->value();
        }

        return QualityScore::create((int) round($total / count($this->metrics)));
    }
}
