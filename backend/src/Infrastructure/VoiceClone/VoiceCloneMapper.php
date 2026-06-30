<?php

declare(strict_types=1);

namespace App\Infrastructure\VoiceClone;

use App\Domain\Translation\Translation;
use App\Domain\Translation\TranslationLanguage;
use App\Domain\TTS\AudioId;
use App\Domain\VoiceClone\VoiceCloneArtifact;
use App\Domain\VoiceClone\VoiceCloneArtifactId;
use App\Domain\VoiceClone\VoiceCloneProvider;
use App\Domain\VoiceClone\VoiceProfile;
use App\Domain\VoiceClone\VoiceProfileId;
use App\Infrastructure\VoiceClone\Exception\OpenVoiceProviderException;

final class VoiceCloneMapper
{
    public function toArtifact(
        string $processOutput,
        Translation $translation,
        VoiceCloneProvider $provider,
        AudioId $clonedAudioId,
        VoiceCloneArtifactId $artifactId,
        AudioId $sourceAudioId,
        string $storagePath,
    ): VoiceCloneArtifact {
        /** @var array<string, mixed>|null $payload */
        $payload = json_decode($processOutput, true);

        if (!is_array($payload)) {
            throw new OpenVoiceProviderException('OpenVoice process output must be valid JSON.');
        }

        $duration = $payload['duration'] ?? null;
        $sampleRate = $payload['sampleRate'] ?? 44100;

        if (!is_numeric($duration)) {
            throw new OpenVoiceProviderException('OpenVoice process output must include duration.');
        }

        if (!is_numeric($sampleRate) || (int) $sampleRate <= 0) {
            throw new OpenVoiceProviderException('OpenVoice process output must include a valid sample rate.');
        }

        $profile = VoiceProfile::create(
            VoiceProfileId::generate(),
            $translation->sourceLanguage(),
            (float) $duration,
            (int) $sampleRate,
        );

        return VoiceCloneArtifact::create(
            $artifactId,
            $profile,
            $provider,
            $clonedAudioId,
            $sourceAudioId,
            $storagePath,
            $translation->targetLanguage(),
        );
    }

    public function sourceLanguageFrom(Translation $translation): TranslationLanguage
    {
        return $translation->sourceLanguage();
    }
}
