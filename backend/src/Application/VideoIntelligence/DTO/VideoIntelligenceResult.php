<?php

declare(strict_types=1);

namespace App\Application\VideoIntelligence\DTO;

use App\Domain\VideoIntelligence\VideoIntelligence;

final readonly class VideoIntelligenceResult
{
    /**
     * @param list<array{index: int, label: string}> $speakers
     */
    public function __construct(
        public string $id,
        public string $videoId,
        public float $durationSeconds,
        public string $scene,
        public string $language,
        public int $speakerCount,
        public string $backgroundNoise,
        public string $backgroundMusic,
        public string $speechSpeed,
        public int $confidence,
        public string $resolution,
        public float $fps,
        public string $lighting,
        public string $lipVisibility,
        public int $faceCount,
        public string $dominantEmotion,
        public float $averageSpeakingRate,
        public int $pauseCount,
        public bool $hasOverlaps,
        public array $speakers,
        public bool $gpuAvailable,
        public float $estimatedVramGb,
    ) {
    }

    public static function fromIntelligence(string $videoId, VideoIntelligence $intelligence): self
    {
        $speakers = [];

        foreach ($intelligence->speakers()->all() as $speaker) {
            $speakers[] = [
                'index' => $speaker->index(),
                'label' => $speaker->label(),
            ];
        }

        return new self(
            $intelligence->id()->value,
            $videoId,
            $intelligence->durationSeconds(),
            $intelligence->scene()->value,
            $intelligence->audio()->language(),
            $intelligence->audio()->speakerCount(),
            $intelligence->audio()->backgroundNoise()->value,
            $intelligence->audio()->backgroundMusic()->value,
            $intelligence->audio()->speechSpeed()->value,
            $intelligence->audio()->confidence()->percentage(),
            $intelligence->visual()->resolution(),
            $intelligence->visual()->fps(),
            $intelligence->visual()->lighting()->value,
            $intelligence->visual()->lipVisibility()->value,
            $intelligence->visual()->faceCount(),
            $intelligence->speech()->dominantEmotion()->value,
            $intelligence->speech()->averageSpeakingRate(),
            $intelligence->speech()->pauseCount(),
            $intelligence->speech()->hasOverlaps(),
            $speakers,
            $intelligence->gpuAvailable(),
            $intelligence->estimatedVramGb(),
        );
    }
}
