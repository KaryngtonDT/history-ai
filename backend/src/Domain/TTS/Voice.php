<?php

declare(strict_types=1);

namespace App\Domain\TTS;

use App\Domain\TTS\Exception\InvalidAudioArtifactException;

final readonly class Voice
{
    public function __construct(
        private string $voiceId,
        private string $displayName,
        private VoiceLanguage $language,
        private VoiceGender $gender,
    ) {
        if ('' === trim($this->voiceId)) {
            throw new InvalidAudioArtifactException('Voice id must not be empty.');
        }

        if ('' === trim($this->displayName)) {
            throw new InvalidAudioArtifactException('Voice display name must not be empty.');
        }
    }

    public static function create(
        string $voiceId,
        string $displayName,
        VoiceLanguage $language,
        VoiceGender $gender,
    ): self {
        return new self($voiceId, $displayName, $language, $gender);
    }

    public function voiceId(): string
    {
        return $this->voiceId;
    }

    public function displayName(): string
    {
        return $this->displayName;
    }

    public function language(): VoiceLanguage
    {
        return $this->language;
    }

    public function gender(): VoiceGender
    {
        return $this->gender;
    }
}
