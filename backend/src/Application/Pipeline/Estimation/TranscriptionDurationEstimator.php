<?php

declare(strict_types=1);

namespace App\Application\Pipeline\Estimation;

use App\Domain\Video\VideoId;

final class TranscriptionDurationEstimator
{
    public function __construct(
        private readonly MediaDurationResolver $mediaDurationResolver,
        private readonly HardwareAwareEstimateResolver $hardwareResolver,
        private readonly string $sttModel,
    ) {
    }

    /**
     * @return array{minSeconds: int, maxSeconds: int, mediaDurationSeconds: int|null, message: string}
     */
    public function estimateForVideo(VideoId $videoId): array
    {
        $duration = $this->mediaDurationResolver->resolveForVideo($videoId);

        return $this->estimate($duration);
    }

    /**
     * @return array{minSeconds: int, maxSeconds: int, mediaDurationSeconds: int|null, message: string}
     */
    public function estimate(?int $mediaDurationSeconds): array
    {
        $profile = EngineSpeedProfile::forModel($this->sttModel, $this->hardwareResolver->hasGpu());

        if (null === $mediaDurationSeconds || $mediaDurationSeconds <= 0) {
            return [
                'minSeconds' => 300,
                'maxSeconds' => 5400,
                'mediaDurationSeconds' => null,
                'message' => 'Local transcription started in background. Estimated duration: 5–90 minutes.',
            ];
        }

        $minSeconds = max(60, (int) round($mediaDurationSeconds * $profile->realTimeFactorMin));
        $maxSeconds = max($minSeconds + 60, (int) round($mediaDurationSeconds * $profile->realTimeFactorMax));

        $minMinutes = max(1, (int) ceil($minSeconds / 60));
        $maxMinutes = max($minMinutes, (int) ceil($maxSeconds / 60));

        return [
            'minSeconds' => $minSeconds,
            'maxSeconds' => $maxSeconds,
            'mediaDurationSeconds' => $mediaDurationSeconds,
            'message' => sprintf(
                'Local transcription started in background. Estimated duration: %d–%d minutes.',
                $minMinutes,
                $maxMinutes,
            ),
        ];
    }
}
