<?php

declare(strict_types=1);

namespace App\Domain\VoiceClone;

use App\Domain\Translation\TranslationLanguage;
use App\Domain\TTS\AudioId;
use App\Domain\VoiceClone\Exception\InvalidVoiceCloneException;

final readonly class VoiceCloneArtifact
{
    public function __construct(
        private VoiceCloneArtifactId $artifactId,
        private VoiceProfile $profile,
        private VoiceCloneProvider $provider,
        private AudioId $clonedAudioId,
        private AudioId $sourceAudioId,
        private string $storagePath,
        private TranslationLanguage $targetLanguage,
    ) {
        if ('' === trim($this->storagePath)) {
            throw new InvalidVoiceCloneException('Voice clone storage path cannot be empty.');
        }
    }

    public static function create(
        VoiceCloneArtifactId $artifactId,
        VoiceProfile $profile,
        VoiceCloneProvider $provider,
        AudioId $clonedAudioId,
        AudioId $sourceAudioId,
        string $storagePath,
        TranslationLanguage $targetLanguage,
    ): self {
        return new self(
            $artifactId,
            $profile,
            $provider,
            $clonedAudioId,
            $sourceAudioId,
            $storagePath,
            $targetLanguage,
        );
    }

    public function artifactId(): VoiceCloneArtifactId
    {
        return $this->artifactId;
    }

    public function profile(): VoiceProfile
    {
        return $this->profile;
    }

    public function provider(): VoiceCloneProvider
    {
        return $this->provider;
    }

    public function clonedAudioId(): AudioId
    {
        return $this->clonedAudioId;
    }

    public function sourceAudioId(): AudioId
    {
        return $this->sourceAudioId;
    }

    public function storagePath(): string
    {
        return $this->storagePath;
    }

    public function targetLanguage(): TranslationLanguage
    {
        return $this->targetLanguage;
    }
}
