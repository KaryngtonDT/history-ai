<?php

declare(strict_types=1);

namespace App\Application\VoiceClone;

use App\Domain\VoiceClone\Exception\InvalidVoiceCloneException;
use App\Domain\VoiceClone\VoiceCloneArtifact;
use App\Domain\VoiceClone\VoiceCloneArtifactId;
use App\Domain\VoiceClone\VoiceCloneProvider;
use App\Domain\VoiceClone\VoiceProfile;
use App\Domain\VoiceClone\VoiceProfileId;
use App\Domain\TTS\AudioId;
use App\Domain\Translation\TranslationId;
use App\Domain\Translation\TranslationLanguage;
use JsonException;

final class VoiceCloneJsonMapper
{
    /**
     * @return array{
     *     artifactId: string,
     *     sourceAudioId: string,
     *     clonedAudioId: string,
     *     targetLanguage: string,
     *     provider: string,
     *     profileId: string,
     *     sourceLanguage: string,
     *     duration: float,
     *     sampleRate: int,
     *     storagePath: string
     * }
     */
    public function toArray(VoiceCloneArtifact $artifact): array
    {
        return [
            'artifactId' => $artifact->artifactId()->value,
            'sourceAudioId' => $artifact->sourceAudioId()->value,
            'clonedAudioId' => $artifact->clonedAudioId()->value,
            'targetLanguage' => $artifact->targetLanguage()->value,
            'provider' => $artifact->provider()->value,
            'profileId' => $artifact->profile()->profileId()->value,
            'sourceLanguage' => $artifact->profile()->language()->value,
            'duration' => $artifact->profile()->duration(),
            'sampleRate' => $artifact->profile()->sampleRate(),
            'storagePath' => $artifact->storagePath(),
        ];
    }

    public function toJson(VoiceCloneArtifact $artifact): string
    {
        return json_encode($this->toArray($artifact), JSON_THROW_ON_ERROR);
    }

    public function fromJson(string $json): VoiceCloneArtifact
    {
        try {
            /** @var mixed $decoded */
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidVoiceCloneException('Stored voice clone is not valid JSON.', 0, $exception);
        }

        if (!is_array($decoded)) {
            throw new InvalidVoiceCloneException('Stored voice clone must be a JSON object.');
        }

        $artifactId = is_string($decoded['artifactId'] ?? null) ? $decoded['artifactId'] : null;
        $sourceAudioId = is_string($decoded['sourceAudioId'] ?? null) ? $decoded['sourceAudioId'] : null;
        $clonedAudioId = is_string($decoded['clonedAudioId'] ?? null) ? $decoded['clonedAudioId'] : null;
        $targetLanguageValue = is_string($decoded['targetLanguage'] ?? null)
            ? $decoded['targetLanguage']
            : TranslationLanguage::Unknown->value;
        $providerValue = is_string($decoded['provider'] ?? null)
            ? $decoded['provider']
            : VoiceCloneProvider::Mock->value;
        $profileId = is_string($decoded['profileId'] ?? null) ? $decoded['profileId'] : null;
        $sourceLanguageValue = is_string($decoded['sourceLanguage'] ?? null)
            ? $decoded['sourceLanguage']
            : TranslationLanguage::Unknown->value;
        $storagePath = is_string($decoded['storagePath'] ?? null) ? $decoded['storagePath'] : null;
        $duration = is_numeric($decoded['duration'] ?? null) ? (float) $decoded['duration'] : null;
        $sampleRate = is_numeric($decoded['sampleRate'] ?? null) ? (int) $decoded['sampleRate'] : null;

        if (
            null === $artifactId
            || null === $sourceAudioId
            || null === $clonedAudioId
            || null === $profileId
            || null === $storagePath
            || null === $duration
            || null === $sampleRate
        ) {
            throw new InvalidVoiceCloneException('Stored voice clone is missing required fields.');
        }

        $targetLanguage = TranslationLanguage::tryFrom($targetLanguageValue) ?? TranslationLanguage::Unknown;
        $sourceLanguage = TranslationLanguage::tryFrom($sourceLanguageValue) ?? TranslationLanguage::Unknown;
        $provider = VoiceCloneProvider::tryFrom($providerValue) ?? VoiceCloneProvider::Mock;

        return VoiceCloneArtifact::create(
            new VoiceCloneArtifactId($artifactId),
            VoiceProfile::create(
                new VoiceProfileId($profileId),
                $sourceLanguage,
                $duration,
                $sampleRate,
            ),
            $provider,
            new AudioId($clonedAudioId),
            new AudioId($sourceAudioId),
            $storagePath,
            $targetLanguage,
        );
    }
}
