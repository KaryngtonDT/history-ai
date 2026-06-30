<?php

declare(strict_types=1);

namespace App\Domain\VoiceClone;

use App\Domain\TTS\AudioId;

final readonly class VoiceCloneArtifact
{
    public function __construct(
        private VoiceCloneArtifactId $artifactId,
        private VoiceProfile $profile,
        private VoiceCloneProvider $provider,
        private AudioId $clonedAudioId,
    ) {
    }

    public static function create(
        VoiceCloneArtifactId $artifactId,
        VoiceProfile $profile,
        VoiceCloneProvider $provider,
        AudioId $clonedAudioId,
    ): self {
        return new self($artifactId, $profile, $provider, $clonedAudioId);
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
}
