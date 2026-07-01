<?php

declare(strict_types=1);

namespace App\Application\Telemetry;

use App\Application\Telemetry\DTO\ProviderStatResult;
use App\Application\Telemetry\DTO\ProviderStatisticsResult;
use App\Application\Telemetry\DTO\RecentErrorResult;
use App\Application\Telemetry\DTO\WorkspaceAnalyticsResult;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\Telemetry\ExecutionMetricType;
use App\Domain\Telemetry\PipelineTelemetry;
use App\Domain\Telemetry\ProviderUsage;

final class WorkspaceAnalyticsAggregator
{
    /**
     * @param list<PipelineTelemetry> $records
     */
    public function aggregate(string $workspaceId, array $records): WorkspaceAnalyticsResult
    {
        if ([] === $records) {
            return new WorkspaceAnalyticsResult(
                0,
                0.0,
                '0s',
                0,
                0.0,
                0.0,
                'n/a',
                'n/a',
                [],
            );
        }

        $processed = count($records);
        $successCount = count(array_filter($records, static fn (PipelineTelemetry $record): bool => $record->success()));
        $processingTimes = array_values(array_filter(array_map(
            static fn (PipelineTelemetry $record): ?float => $record->processingTimeSeconds(),
            $records,
        )));
        $qualityScores = array_values(array_filter(array_map(
            static fn (PipelineTelemetry $record): ?int => $record->qualityScore(),
            $records,
        )));
        $gpuUsages = [];

        foreach ($records as $record) {
            $gpu = $record->metrics()->findByType(ExecutionMetricType::GpuUsage);

            if (null !== $gpu) {
                $gpuUsages[] = $gpu->value();
            }
        }

        $averageProcessing = [] === $processingTimes
            ? 0.0
            : array_sum($processingTimes) / count($processingTimes);
        $averageQuality = [] === $qualityScores
            ? 0
            : (int) round(array_sum($qualityScores) / count($qualityScores));
        $averageGpu = [] === $gpuUsages
            ? 0.0
            : array_sum($gpuUsages) / count($gpuUsages);

        return new WorkspaceAnalyticsResult(
            $processed,
            $averageProcessing,
            $this->formatDuration($averageProcessing),
            $averageQuality,
            round(($successCount / $processed) * 100, 1),
            round($averageGpu, 1),
            $this->topProviderForStage($records, PipelineStageType::Translation->value, 'n/a'),
            $this->topProviderForStage($records, PipelineStageType::TextToSpeech->value, 'n/a'),
            $this->recentErrors($records),
        );
    }

    /**
     * @param list<PipelineTelemetry> $records
     */
    public function providerStatistics(array $records): ProviderStatisticsResult
    {
        /** @var array<string, array{stage: string, providerId: string, count: int, duration: float}> $indexed */
        $indexed = [];

        foreach ($records as $record) {
            foreach ($record->providerUsages()->all() as $usage) {
                $key = $usage->stage().':'.$usage->providerId();
                $existing = $indexed[$key] ?? [
                    'stage' => $usage->stage(),
                    'providerId' => $usage->providerId(),
                    'count' => 0,
                    'duration' => 0.0,
                ];
                $existing['count'] += $usage->invocationCount();
                $existing['duration'] += $usage->totalDurationSeconds();
                $indexed[$key] = $existing;
            }
        }

        $providers = array_map(
            static fn (array $entry): ProviderStatResult => new ProviderStatResult(
                $entry['stage'],
                $entry['providerId'],
                $entry['count'],
                $entry['count'] > 0 ? round($entry['duration'] / $entry['count'], 2) : 0.0,
            ),
            array_values($indexed),
        );

        usort(
            $providers,
            static fn (ProviderStatResult $left, ProviderStatResult $right): int => $right->invocationCount <=> $left->invocationCount,
        );

        return new ProviderStatisticsResult($providers);
    }

    /**
     * @param list<PipelineTelemetry> $records
     */
    private function topProviderForStage(array $records, string $stage, string $fallback): string
    {
        /** @var array<string, int> $counts */
        $counts = [];

        foreach ($records as $record) {
            foreach ($record->providerUsages()->forStage($stage) as $usage) {
                $counts[$usage->providerId()] = ($counts[$usage->providerId()] ?? 0) + $usage->invocationCount();
            }
        }

        if ([] === $counts) {
            return $fallback;
        }

        arsort($counts);

        return (string) array_key_first($counts);
    }

    /**
     * @param list<PipelineTelemetry> $records
     *
     * @return list<RecentErrorResult>
     */
    private function recentErrors(array $records): array
    {
        $errors = array_values(array_filter(
            $records,
            static fn (PipelineTelemetry $record): bool => !$record->success() && null !== $record->errorMessage(),
        ));

        usort(
            $errors,
            static fn (PipelineTelemetry $left, PipelineTelemetry $right): int => $right->recordedAt() <=> $left->recordedAt(),
        );

        return array_map(
            static fn (PipelineTelemetry $record): RecentErrorResult => new RecentErrorResult(
                (string) $record->errorMessage(),
                str_contains(strtolower((string) $record->errorMessage()), 'retry') ? 'Recovered' : 'Resolved',
                $record->recordedAt()->format(\DateTimeInterface::ATOM),
            ),
            array_slice($errors, 0, 5),
        );
    }

    private function formatDuration(float $seconds): string
    {
        $totalSeconds = max(0, (int) round($seconds));
        $minutes = intdiv($totalSeconds, 60);
        $remaining = $totalSeconds % 60;

        if ($minutes > 0) {
            return sprintf('%dm %02ds', $minutes, $remaining);
        }

        return sprintf('%ds', $remaining);
    }
}
