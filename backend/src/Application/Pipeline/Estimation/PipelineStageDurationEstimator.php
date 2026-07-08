<?php

declare(strict_types=1);

namespace App\Application\Pipeline\Estimation;

use App\Domain\Pipeline\PipelineStageType;
use App\Domain\Video\VideoId;

final class PipelineStageDurationEstimator
{
    public function __construct(
        private readonly TranscriptionDurationEstimator $transcriptionDurationEstimator,
        private readonly MediaDurationResolver $mediaDurationResolver,
    ) {
    }

    /**
     * @return array{minSeconds: int, maxSeconds: int, mediaDurationSeconds: int|null, message: string}
     */
    public function estimateForStage(VideoId $videoId, PipelineStageType $stage): array
    {
        if (PipelineStageType::SpeechToText === $stage) {
            return $this->transcriptionDurationEstimator->estimateForVideo($videoId);
        }

        $mediaDuration = $this->mediaDurationResolver->resolveForVideo($videoId);
        $maxSeconds = $this->estimateSeconds($stage, $mediaDuration);
        $minSeconds = max(60, (int) round($maxSeconds * 0.7));
        $minMinutes = max(1, (int) ceil($minSeconds / 60));
        $maxMinutes = max($minMinutes, (int) ceil($maxSeconds / 60));

        return [
            'minSeconds' => $minSeconds,
            'maxSeconds' => $maxSeconds,
            'mediaDurationSeconds' => $mediaDuration,
            'message' => sprintf(
                'Estimated duration: %d–%d minutes.',
                $minMinutes,
                $maxMinutes,
            ),
        ];
    }

    private function estimateSeconds(PipelineStageType $stage, ?int $mediaDurationSeconds): int
    {
        if (null === $mediaDurationSeconds || $mediaDurationSeconds <= 0) {
            return match ($stage) {
                PipelineStageType::Translation => 600,
                PipelineStageType::TextToSpeech => 900,
                PipelineStageType::VoiceClone => 1200,
                PipelineStageType::LipSync => 1800,
                PipelineStageType::VideoRender => 900,
                default => 600,
            };
        }

        return match ($stage) {
            PipelineStageType::Translation => max(120, (int) ceil($mediaDurationSeconds / 20)),
            PipelineStageType::TextToSpeech => max(180, (int) ceil($mediaDurationSeconds / 15)),
            PipelineStageType::VoiceClone => max(240, (int) ceil($mediaDurationSeconds / 8)),
            PipelineStageType::LipSync => max(300, (int) ceil($mediaDurationSeconds / 6)),
            PipelineStageType::VideoRender => max(180, (int) ceil($mediaDurationSeconds / 12)),
            default => max(120, (int) ceil($mediaDurationSeconds / 20)),
        };
    }
}
