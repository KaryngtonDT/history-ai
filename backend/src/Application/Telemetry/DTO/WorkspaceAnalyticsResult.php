<?php

declare(strict_types=1);

namespace App\Application\Telemetry\DTO;

final readonly class WorkspaceAnalyticsResult
{
    /**
     * @param list<RecentErrorResult> $recentErrors
     */
    public function __construct(
        public int $processedVideos,
        public float $averageProcessingTimeSeconds,
        public string $averageProcessingTimeLabel,
        public int $averageQuality,
        public float $successRate,
        public float $gpuUsagePercent,
        public string $topTranslationProvider,
        public string $topTtsProvider,
        public array $recentErrors,
    ) {
    }
}
