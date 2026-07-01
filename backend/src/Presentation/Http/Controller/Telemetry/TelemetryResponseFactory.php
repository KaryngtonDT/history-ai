<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Telemetry;

use App\Application\Telemetry\DTO\PipelineTelemetryResult;
use App\Application\Telemetry\DTO\ProviderStatResult;
use App\Application\Telemetry\DTO\ProviderStatisticsResult;
use App\Application\Telemetry\DTO\RecentErrorResult;
use App\Application\Telemetry\DTO\WorkspaceAnalyticsResult;

final class TelemetryResponseFactory
{
    /**
     * @return array<string, mixed>
     */
    public static function analyticsFromResult(WorkspaceAnalyticsResult $result): array
    {
        return [
            'processedVideos' => $result->processedVideos,
            'averageProcessingTimeSeconds' => $result->averageProcessingTimeSeconds,
            'averageProcessingTimeLabel' => $result->averageProcessingTimeLabel,
            'averageQuality' => $result->averageQuality,
            'successRate' => $result->successRate,
            'gpuUsagePercent' => $result->gpuUsagePercent,
            'topTranslationProvider' => $result->topTranslationProvider,
            'topTtsProvider' => $result->topTtsProvider,
            'recentErrors' => array_map(
                static fn (RecentErrorResult $error): array => self::recentErrorFromResult($error),
                $result->recentErrors,
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function providerStatisticsFromResult(ProviderStatisticsResult $result): array
    {
        return [
            'providers' => array_map(
                static fn (ProviderStatResult $provider): array => self::providerStatFromResult($provider),
                $result->providers,
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function telemetryFromResult(PipelineTelemetryResult $result): array
    {
        return [
            'id' => $result->id,
            'workspaceId' => $result->workspaceId,
            'videoId' => $result->videoId,
            'success' => $result->success,
            'metrics' => $result->metrics,
            'providerUsages' => $result->providerUsages,
            'recordedAt' => $result->recordedAt,
            'batchJobId' => $result->batchJobId,
            'qualityScore' => $result->qualityScore,
            'errorMessage' => $result->errorMessage,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function recentErrorFromResult(RecentErrorResult $error): array
    {
        return [
            'message' => $error->message,
            'status' => $error->status,
            'recordedAt' => $error->recordedAt,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function providerStatFromResult(ProviderStatResult $provider): array
    {
        return [
            'stage' => $provider->stage,
            'providerId' => $provider->providerId,
            'invocationCount' => $provider->invocationCount,
            'averageDurationSeconds' => $provider->averageDurationSeconds,
        ];
    }
}
