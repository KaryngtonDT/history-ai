<?php

declare(strict_types=1);

namespace App\Domain\VoiceClone;

use App\Domain\Translation\TranslationLanguage;
use App\Domain\VoiceClone\Exception\InvalidVoiceCloneException;

final readonly class VoiceProfile
{
    public function __construct(
        private VoiceProfileId $profileId,
        private TranslationLanguage $sourceLanguage,
        private float $duration,
        private int $sampleRate,
    ) {
        if ($this->duration < 0) {
            throw new InvalidVoiceCloneException('Voice profile duration cannot be negative.');
        }

        if ($this->sampleRate <= 0) {
            throw new InvalidVoiceCloneException('Voice profile sample rate must be positive.');
        }
    }

    public static function create(
        VoiceProfileId $profileId,
        TranslationLanguage $sourceLanguage,
        float $duration,
        int $sampleRate,
    ): self {
        return new self($profileId, $sourceLanguage, $duration, $sampleRate);
    }

    public function profileId(): VoiceProfileId
    {
        return $this->profileId;
    }

    public function duration(): float
    {
        return $this->duration;
    }

    public function sampleRate(): int
    {
        return $this->sampleRate;
    }

    public function language(): TranslationLanguage
    {
        return $this->sourceLanguage;
    }
}
