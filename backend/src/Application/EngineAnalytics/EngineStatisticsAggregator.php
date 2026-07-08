<?php

declare(strict_types=1);

namespace App\Application\EngineAnalytics;

use App\Domain\EngineAnalytics\EngineExecutionHistory;
use App\Domain\EngineAnalytics\EngineExecutionHistoryRepositoryInterface;
use App\Domain\EngineAnalytics\EngineExecutionStatus;
use App\Domain\Pipeline\PipelineStageType;

final class EngineStatisticsAggregator
{
    public function __construct(
        private readonly EngineExecutionHistoryRepositoryInterface $historyRepository,
        private readonly DurationPredictionEngine $predictionEngine,
    ) {
    }

    public function refreshAfterExecution(): void
    {
        $this->predictionEngine->invalidateCache();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function aggregateEngines(?PipelineStageType $stage = null): array
    {
        $executions = $this->historyRepository->findRecent(stage: $stage, limit: 500);
        /** @var array<string, list<EngineExecutionHistory>> $grouped */
        $grouped = [];

        foreach ($executions as $execution) {
            $grouped[$execution->engineId()][] = $execution;
        }

        $fastestMedian = null;
        $medians = [];

        foreach ($grouped as $engineId => $items) {
            $completed = array_values(array_filter(
                $items,
                static fn (EngineExecutionHistory $item): bool => EngineExecutionStatus::Completed === $item->status(),
            ));

            if ([] === $completed) {
                continue;
            }

            $durations = array_map(
                static fn (EngineExecutionHistory $item): int => $item->actualDurationSeconds(),
                $completed,
            );
            sort($durations);
            $median = $durations[(int) floor((count($durations) - 1) / 2)];
            $medians[$engineId] = $median;
            $fastestMedian = null === $fastestMedian ? $median : min($fastestMedian, $median);
        }

        $engines = [];

        foreach ($grouped as $engineId => $items) {
            $engines[] = $this->buildEngineSummary($engineId, $items, $medians[$engineId] ?? null, $fastestMedian);
        }

        usort(
            $engines,
            static fn (array $left, array $right): int => ($left['averageDurationSeconds'] ?? PHP_INT_MAX)
                <=> ($right['averageDurationSeconds'] ?? PHP_INT_MAX),
        );

        return $engines;
    }

    /**
     * @param list<EngineExecutionHistory> $items
     *
     * @return array<string, mixed>
     */
    private function buildEngineSummary(
        string $engineId,
        array $items,
        ?int $median,
        ?int $fastestMedian,
    ): array {
        $completed = array_values(array_filter(
            $items,
            static fn (EngineExecutionHistory $item): bool => EngineExecutionStatus::Completed === $item->status(),
        ));
        $failed = array_values(array_filter(
            $items,
            static fn (EngineExecutionHistory $item): bool => EngineExecutionStatus::Failed === $item->status(),
        ));
        $durations = array_map(
            static fn (EngineExecutionHistory $item): int => $item->actualDurationSeconds(),
            $completed,
        );
        sort($durations);
        $errors = array_map(
            static fn (EngineExecutionHistory $item): float => abs($item->estimationErrorSeconds()),
            $completed,
        );
        $averageDuration = [] === $durations
            ? null
            : (int) round(array_sum($durations) / count($durations));
        $averageErrorPercent = [] === $errors
            ? null
            : round(array_sum($errors) / count($errors), 1);
        $relativeSpeedScore = null !== $median && null !== $fastestMedian && $median > 0
            ? max(1, min(5, (int) round(5 * $fastestMedian / $median)))
            : null;

        return [
            'engineId' => $engineId,
            'executionCount' => count($items),
            'completedCount' => count($completed),
            'failedCount' => count($failed),
            'successRate' => 0 === count($items) ? null : round(count($completed) / count($items) * 100, 1),
            'failureRate' => 0 === count($items) ? null : round(count($failed) / count($items) * 100, 1),
            'averageDurationSeconds' => $averageDuration,
            'medianDurationSeconds' => $median,
            'fastestDurationSeconds' => [] === $durations ? null : min($durations),
            'slowestDurationSeconds' => [] === $durations ? null : max($durations),
            'averageEstimationErrorSeconds' => $averageErrorPercent,
            'relativeSpeedScore' => $relativeSpeedScore,
            'relativeSpeedLabel' => null === $relativeSpeedScore ? null : str_repeat('⭐', $relativeSpeedScore),
            'hardwareProfiles' => array_values(array_unique(array_map(
                static fn (EngineExecutionHistory $item): string => $item->hardwareProfile(),
                $items,
            ))),
        ];
    }
}
