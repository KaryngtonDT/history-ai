<?php

declare(strict_types=1);

namespace App\Domain\VideoIntelligence;

use App\Domain\VideoIntelligence\Exception\InvalidVideoIntelligenceException;

final readonly class VideoIntelligence
{
    public function __construct(
        private VideoIntelligenceId $id,
        private float $durationSeconds,
        private VideoScene $scene,
        private AudioCharacteristics $audio,
        private VisualCharacteristics $visual,
        private SpeechCharacteristics $speech,
        private VideoSpeakerCollection $speakers,
        private bool $gpuAvailable,
        private float $estimatedVramGb,
    ) {
        if ($this->durationSeconds < 0) {
            throw new InvalidVideoIntelligenceException('Duration cannot be negative.');
        }

        if ($this->estimatedVramGb < 0) {
            throw new InvalidVideoIntelligenceException('Estimated VRAM cannot be negative.');
        }
    }

    public static function create(
        VideoIntelligenceId $id,
        float $durationSeconds,
        VideoScene $scene,
        AudioCharacteristics $audio,
        VisualCharacteristics $visual,
        SpeechCharacteristics $speech,
        VideoSpeakerCollection $speakers,
        bool $gpuAvailable,
        float $estimatedVramGb,
    ): self {
        return new self(
            $id,
            $durationSeconds,
            $scene,
            $audio,
            $visual,
            $speech,
            $speakers,
            $gpuAvailable,
            $estimatedVramGb,
        );
    }

    public function id(): VideoIntelligenceId
    {
        return $this->id;
    }

    public function durationSeconds(): float
    {
        return $this->durationSeconds;
    }

    public function scene(): VideoScene
    {
        return $this->scene;
    }

    public function audio(): AudioCharacteristics
    {
        return $this->audio;
    }

    public function visual(): VisualCharacteristics
    {
        return $this->visual;
    }

    public function speech(): SpeechCharacteristics
    {
        return $this->speech;
    }

    public function speakers(): VideoSpeakerCollection
    {
        return $this->speakers;
    }

    public function gpuAvailable(): bool
    {
        return $this->gpuAvailable;
    }

    public function estimatedVramGb(): float
    {
        return $this->estimatedVramGb;
    }
}
