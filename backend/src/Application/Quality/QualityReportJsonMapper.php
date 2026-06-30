<?php

declare(strict_types=1);

namespace App\Application\Quality;

use App\Domain\Quality\Exception\InvalidQualityReportException;
use App\Domain\Quality\PublicationRecommendation;
use App\Domain\Quality\QualityCategory;
use App\Domain\Quality\QualityMetric;
use App\Domain\Quality\QualityMetricCollection;
use App\Domain\Quality\QualityReport;
use App\Domain\Quality\QualityReportId;
use App\Domain\Quality\QualityScore;
use JsonException;

final class QualityReportJsonMapper
{
    /**
     * @return array{
     *     id: string,
     *     overallScore: int,
     *     recommendation: string,
     *     metrics: list<array{category: string, score: int, explanation: string}>,
     *     explanations: list<string>
     * }
     */
    public function toArray(QualityReport $report): array
    {
        $metrics = [];

        foreach ($report->metrics()->all() as $metric) {
            $metrics[] = [
                'category' => $metric->category()->value,
                'score' => $metric->score()->value(),
                'explanation' => $metric->explanation(),
            ];
        }

        return [
            'id' => $report->id()->value,
            'overallScore' => $report->overallScore()->value(),
            'recommendation' => $report->recommendation()->value,
            'metrics' => $metrics,
            'explanations' => $report->explanations(),
        ];
    }

    public function toJson(QualityReport $report): string
    {
        return json_encode($this->toArray($report), JSON_THROW_ON_ERROR);
    }

    public function fromJson(string $json): QualityReport
    {
        try {
            /** @var mixed $decoded */
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidQualityReportException('Stored quality report is not valid JSON.', 0, $exception);
        }

        if (!is_array($decoded)) {
            throw new InvalidQualityReportException('Stored quality report must be a JSON object.');
        }

        $id = is_string($decoded['id'] ?? null) ? $decoded['id'] : null;
        $overallScore = is_numeric($decoded['overallScore'] ?? null) ? (int) $decoded['overallScore'] : null;
        $recommendationValue = is_string($decoded['recommendation'] ?? null) ? $decoded['recommendation'] : null;
        $metricsData = is_array($decoded['metrics'] ?? null) ? $decoded['metrics'] : null;
        $explanations = is_array($decoded['explanations'] ?? null) ? $decoded['explanations'] : [];

        if (null === $id || null === $overallScore || null === $recommendationValue || null === $metricsData) {
            throw new InvalidQualityReportException('Stored quality report is missing required fields.');
        }

        $metrics = [];

        foreach ($metricsData as $metricData) {
            if (!is_array($metricData)) {
                continue;
            }

            $categoryValue = is_string($metricData['category'] ?? null) ? $metricData['category'] : null;
            $score = is_numeric($metricData['score'] ?? null) ? (int) $metricData['score'] : null;
            $explanation = is_string($metricData['explanation'] ?? null) ? $metricData['explanation'] : '';

            if (null === $categoryValue || null === $score) {
                continue;
            }

            $category = QualityCategory::tryFrom($categoryValue);

            if (null === $category || QualityCategory::Overall === $category) {
                continue;
            }

            $metrics[] = QualityMetric::create($category, QualityScore::create($score), $explanation);
        }

        $recommendation = PublicationRecommendation::tryFrom($recommendationValue)
            ?? PublicationRecommendation::ReviewRecommended;

        return QualityReport::create(
            new QualityReportId($id),
            new QualityMetricCollection($metrics),
            QualityScore::create($overallScore),
            $recommendation,
            array_values(array_filter($explanations, is_string(...))),
        );
    }
}
