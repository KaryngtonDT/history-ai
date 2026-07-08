<?php

declare(strict_types=1);

namespace App\Application\EngineAnalytics;

use App\Domain\EngineAnalytics\EngineExecutionHistoryRepositoryInterface;
use App\Domain\Pipeline\PipelineStageType;

final class EngineAnalyticsContextBuilder
{
    public function __construct(
        private readonly EngineStatisticsAggregator $statisticsAggregator,
        private readonly PipelineJobAnalyticsEnricher $analyticsEnricher,
        private readonly EngineExecutionHistoryRepositoryInterface $historyRepository,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function buildForStage(?PipelineStageType $stage = null): array
    {
        $engines = $this->statisticsAggregator->aggregateEngines($stage);
        $recent = $this->analyticsEnricher->listExecutions($stage, null, null, 10);

        return [
            'stage' => $stage?->value,
            'engines' => $engines,
            'recentExecutions' => $recent,
            'fastestEngine' => $engines[0] ?? null,
            'summary' => $this->buildSummary($engines, $stage),
        ];
    }

    /**
     * @param list<array<string, mixed>> $engines
     */
    private function buildSummary(array $engines, ?PipelineStageType $stage): string
    {
        if ([] === $engines) {
            return 'No engine execution history recorded yet. Estimates use default rules until enough jobs complete.';
        }

        $fastest = $engines[0];
        $stageLabel = $stage?->value ?? 'pipeline';

        return sprintf(
            'Based on %d recorded executions for %s, %s is currently the fastest engine on this hardware (median %s).',
            (int) ($fastest['executionCount'] ?? 0),
            $stageLabel,
            (string) ($fastest['engineId'] ?? 'unknown'),
            $this->formatDuration((int) ($fastest['medianDurationSeconds'] ?? 0)),
        );
    }

    private function formatDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return sprintf('%ds', $seconds);
        }

        return sprintf('%d min', (int) ceil($seconds / 60));
    }
}
