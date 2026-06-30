<?php

declare(strict_types=1);

namespace App\Infrastructure\TTS;

use App\Domain\Translation\Translation;
use App\Domain\Translation\TranslationId;
use App\Domain\TTS\AudioArtifact;
use App\Domain\TTS\AudioId;
use App\Domain\TTS\FileFormat;
use App\Domain\TTS\TextToSpeechProvider;
use App\Domain\TTS\Voice;
use App\Infrastructure\TTS\Exception\F5TextToSpeechProviderException;

final class AudioMapper
{
    public function toArtifact(
        string $processOutput,
        TranslationId $translationId,
        TextToSpeechProvider $provider,
        Voice $voice,
        AudioId $audioId,
    ): AudioArtifact {
        /** @var array<string, mixed>|null $payload */
        $payload = json_decode($processOutput, true);

        if (!is_array($payload)) {
            throw new F5TextToSpeechProviderException('F5 process output must be valid JSON.');
        }

        $duration = $payload['duration'] ?? null;
        $format = $payload['format'] ?? 'wav';

        if (!is_numeric($duration)) {
            throw new F5TextToSpeechProviderException('F5 process output must include duration.');
        }

        $fileFormat = match (strtolower((string) $format)) {
            'wav' => FileFormat::Wav,
            'mp3' => FileFormat::Mp3,
            default => throw new F5TextToSpeechProviderException(sprintf(
                'Unsupported audio format "%s".',
                $format,
            )),
        };

        return AudioArtifact::create(
            $audioId,
            $translationId,
            $provider,
            $voice,
            (float) $duration,
            $fileFormat,
        );
    }

    public function translationIdFrom(Translation $translation): TranslationId
    {
        return $translation->translationId();
    }
}
