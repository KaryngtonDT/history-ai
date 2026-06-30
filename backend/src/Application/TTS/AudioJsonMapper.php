<?php

declare(strict_types=1);

namespace App\Application\TTS;

use App\Domain\TTS\Exception\InvalidAudioArtifactException;
use App\Domain\TTS\AudioArtifact;
use App\Domain\TTS\AudioId;
use App\Domain\TTS\FileFormat;
use App\Domain\TTS\TextToSpeechProvider;
use App\Domain\TTS\Voice;
use App\Domain\TTS\VoiceGender;
use App\Domain\TTS\VoiceLanguage;
use App\Domain\Translation\TranslationId;
use App\Domain\Translation\TranslationLanguage;
use JsonException;

final class AudioJsonMapper
{
    /**
     * @return array{
     *     audioId: string,
     *     translationId: string,
     *     targetLanguage: string,
     *     provider: string,
     *     voiceId: string,
     *     voiceDisplayName: string,
     *     voiceLanguage: string,
     *     voiceGender: string,
     *     duration: float,
     *     format: string,
     *     storagePath: string
     * }
     */
    public function toArray(AudioArtifact $audio): array
    {
        return [
            'audioId' => $audio->audioId()->value,
            'translationId' => $audio->translationId()->value,
            'targetLanguage' => $audio->targetLanguage()->value,
            'provider' => $audio->provider()->value,
            'voiceId' => $audio->voice()->voiceId(),
            'voiceDisplayName' => $audio->voice()->displayName(),
            'voiceLanguage' => $audio->voice()->language()->value,
            'voiceGender' => $audio->voice()->gender()->value,
            'duration' => $audio->duration(),
            'format' => $audio->format()->value,
            'storagePath' => $audio->storagePath(),
        ];
    }

    public function toJson(AudioArtifact $audio): string
    {
        return json_encode($this->toArray($audio), JSON_THROW_ON_ERROR);
    }

    public function fromJson(string $json): AudioArtifact
    {
        try {
            /** @var mixed $decoded */
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidAudioArtifactException('Stored audio is not valid JSON.', 0, $exception);
        }

        if (!is_array($decoded)) {
            throw new InvalidAudioArtifactException('Stored audio must be a JSON object.');
        }

        $audioId = is_string($decoded['audioId'] ?? null) ? $decoded['audioId'] : null;
        $translationId = is_string($decoded['translationId'] ?? null) ? $decoded['translationId'] : null;
        $targetLanguageValue = is_string($decoded['targetLanguage'] ?? null)
            ? $decoded['targetLanguage']
            : TranslationLanguage::Unknown->value;
        $providerValue = is_string($decoded['provider'] ?? null)
            ? $decoded['provider']
            : TextToSpeechProvider::Mock->value;
        $voiceId = is_string($decoded['voiceId'] ?? null) ? $decoded['voiceId'] : null;
        $voiceDisplayName = is_string($decoded['voiceDisplayName'] ?? null) ? $decoded['voiceDisplayName'] : null;
        $voiceLanguageValue = is_string($decoded['voiceLanguage'] ?? null)
            ? $decoded['voiceLanguage']
            : VoiceLanguage::English->value;
        $voiceGenderValue = is_string($decoded['voiceGender'] ?? null)
            ? $decoded['voiceGender']
            : VoiceGender::Neutral->value;
        $storagePath = is_string($decoded['storagePath'] ?? null) ? $decoded['storagePath'] : null;
        $duration = is_numeric($decoded['duration'] ?? null) ? (float) $decoded['duration'] : null;
        $formatValue = is_string($decoded['format'] ?? null) ? $decoded['format'] : FileFormat::Wav->value;

        if (null === $audioId || null === $translationId || null === $voiceId || null === $voiceDisplayName || null === $storagePath || null === $duration) {
            throw new InvalidAudioArtifactException('Stored audio is missing required fields.');
        }

        $targetLanguage = TranslationLanguage::tryFrom($targetLanguageValue) ?? TranslationLanguage::Unknown;
        $provider = TextToSpeechProvider::tryFrom($providerValue) ?? TextToSpeechProvider::Mock;
        $voiceLanguage = VoiceLanguage::tryFrom($voiceLanguageValue) ?? VoiceLanguage::English;
        $voiceGender = VoiceGender::tryFrom($voiceGenderValue) ?? VoiceGender::Neutral;
        $format = FileFormat::tryFrom($formatValue) ?? FileFormat::Wav;

        return AudioArtifact::create(
            new AudioId($audioId),
            new TranslationId($translationId),
            $provider,
            Voice::create($voiceId, $voiceDisplayName, $voiceLanguage, $voiceGender),
            $duration,
            $format,
            $storagePath,
            $targetLanguage,
        );
    }
}
