<?php

declare(strict_types=1);

namespace App\Infrastructure\VoiceClone;

use App\Domain\Translation\Translation;
use App\Domain\TTS\AudioArtifact;
use App\Domain\TTS\AudioId;
use App\Domain\VoiceClone\VoiceCloneArtifact;
use App\Domain\VoiceClone\VoiceCloneArtifactId;
use App\Domain\VoiceClone\VoiceCloneProvider;
use App\Domain\VoiceClone\VoiceCloneProviderInterface;
use App\Domain\VoiceClone\VoiceProfile;
use App\Domain\VoiceClone\VoiceProfileId;

final class MockVoiceCloneProvider implements VoiceCloneProviderInterface
{
    public function cloneVoice(AudioArtifact $source, Translation $translation): VoiceCloneArtifact
    {
        $duration = max(1.0, $source->duration());

        return VoiceCloneArtifact::create(
            VoiceCloneArtifactId::generate(),
            VoiceProfile::create(
                VoiceProfileId::generate(),
                $translation->sourceLanguage(),
                $duration,
                44100,
            ),
            VoiceCloneProvider::Mock,
            AudioId::generate(),
            $source->audioId(),
            '/tmp/mock-cloned.wav',
            $translation->targetLanguage(),
        );
    }
}
