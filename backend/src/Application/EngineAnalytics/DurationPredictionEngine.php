<?php

declare(strict_types=1);

namespace App\Application\EngineAnalytics;

use App\Application\Pipeline\Estimation\PipelineStageDurationEstimator;
use App\Application\Runtime\RuntimePlatformInterface;
use App\Domain\EngineAnalytics\EngineExecutionHistoryRepositoryInterface;
use App\Domain\EngineAnalytics\EngineExecutionStatus;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\Video\VideoId;

final class DurationPredictionEngine
{
    private const int MIN_SAMPLES = 3;

    private const int WINDOW_SIZE = 20;

    /** @var array<string, array{maxSeconds: int, minSeconds: int, confidence: float, source: string}> */
    private array $cache = [];

    public function __construct(
        private readonly EngineExecutionHistoryRepositoryInterface $historyRepository,
        private readonly PipelineStageDurationEstimator $fallbackEstimator,
        private readonly RuntimePlatformInterface $runtimePlatform,
        private readonly int $minSamples = self::MIN_SAMPLES,
    ) {
    }

    /**
     * @return array{
     *     minSeconds: int,
     *     maxSeconds: int,
     *     confidence: float,
     *     source: string,
     *     message: string,
     *     mediaDurationSeconds: int|null
     * }
     */
    public function estimateForStage(
        VideoId $videoId,
        PipelineStageType $stage,
        ?string $engineId = null,
    ): array {
        $engineId ??= $this->defaultEngineForStage($stage);
        $hardwareProfile = $this->resolveHardwareProfile();
        $cacheKey = sprintf('%s:%s:%s', $stage->value, $engineId, $hardwareProfile);

        if (isset($this->cache[$cacheKey])) {
            return $this->mergeWithFallbackMessage($this->cache[$cacheKey], $videoId, $stage);
        }

        $samples = $this->historyRepository->findRecent(
            stage: $stage,
            engineId: $engineId,
            hardwareProfile: $hardwareProfile,
            limit: self::WINDOW_SIZE,
        );
        $completedSamples = array_values(array_filter(
            $samples,
            static fn ($sample) => EngineExecutionStatus::Completed === $sample->status(),
        ));

        if (count($completedSamples) >= $this->minSamples) {
            $durations = array_map(
                static fn ($sample) => $sample->actualDurationSeconds(),
                $completedSamples,
            );
            sort($durations);
            $median = $durations[(int) floor((count($durations) - 1) / 2)];
            $p75Index = (int) floor((count($durations) - 1) * 0.75);
            $p75 = $durations[$p75Index];
            $confidence = min(1.0, count($completedSamples) / self::WINDOW_SIZE);

            $result = [
                'minSeconds' => max(60, (int) round($median * 0.8)),
                'maxSeconds' => max(60, $p75),
                'confidence' => round($confidence, 2),
                'source' => 'historical',
                'message' => sprintf(
                    'Estimated from %d recent executions on %s.',
                    count($completedSamples),
                    $hardwareProfile,
                ),
                'mediaDurationSeconds' => null,
            ];
            $this->cache[$cacheKey] = $result;

            return $this->mergeWithFallbackMessage($result, $videoId, $stage);
        }

        $fallback = $this->fallbackEstimator->estimateForStage($videoId, $stage);
        $result = [
            'minSeconds' => $fallback['minSeconds'],
            'maxSeconds' => $fallback['maxSeconds'],
            'confidence' => 0.2,
            'source' => 'rules',
            'message' => $fallback['message'],
            'mediaDurationSeconds' => $fallback['mediaDurationSeconds'] ?? null,
        ];
        $this->cache[$cacheKey] = $result;

        return $result;
    }

    public function invalidateCache(): void
    {
        $this->cache = [];
    }

    private function mergeWithFallbackMessage(array $result, VideoId $videoId, PipelineStageType $stage): array
    {
        if (null === $result['mediaDurationSeconds']) {
            $fallback = $this->fallbackEstimator->estimateForStage($videoId, $stage);
            $result['mediaDurationSeconds'] = $fallback['mediaDurationSeconds'] ?? null;
        }

        return $result;
    }

    private function resolveHardwareProfile(): string
    {
        $profile = $this->runtimePlatform->hardwareProfile();

        return is_string($profile['profile']['type'] ?? null)
            ? $profile['profile']['type']
            : 'unknown';
    }

    private function defaultEngineForStage(PipelineStageType $stage): string
    {
        return match ($stage) {
            PipelineStageType::SpeechToText => 'faster_whisper',
            PipelineStageType::Translation => 'ollama',
            PipelineStageType::TextToSpeech => 'f5_tts',
            PipelineStageType::VoiceClone => 'openvoice',
            PipelineStageType::LipSync => 'latentsync',
            PipelineStageType::VideoRender => 'ffmpeg',
        };
    }
}
