<?php

declare(strict_types=1);

namespace App\Application\Quality\DTO;

use App\Domain\Quality\QualityMetric;
use App\Domain\Quality\QualityReport;

final readonly class QualityReportResult
{
    /**
     * @param list<array{
     *     category: string,
     *     score: int,
     *     explanation: string
     * }> $metrics
     * @param list<string> $explanations
     */
    public function __construct(
        public string $id,
        public string $videoId,
        public int $overallScore,
        public string $recommendation,
        public array $metrics,
        public array $explanations,
    ) {
    }

    public static function fromReport(string $videoId, QualityReport $report): self
    {
        $metrics = array_map(
            static fn (QualityMetric $metric): array => [
                'category' => $metric->category()->value,
                'score' => $metric->score()->value(),
                'explanation' => $metric->explanation(),
            ],
            $report->metrics()->all(),
        );

        return new self(
            $report->id()->value,
            $videoId,
            $report->overallScore()->value(),
            $report->recommendation()->value,
            $metrics,
            $report->explanations(),
        );
    }
}
