<?php

declare(strict_types=1);

namespace App\Infrastructure\TTS;

use App\Domain\Translation\Translation;
use App\Domain\Translation\TranslationLanguage;
use App\Domain\TTS\AudioArtifact;
use App\Domain\TTS\AudioId;
use App\Domain\TTS\FileFormat;
use App\Domain\TTS\TextToSpeechProvider;
use App\Domain\TTS\TextToSpeechProviderInterface;
use App\Domain\TTS\Voice;

final class MockTextToSpeechProvider implements TextToSpeechProviderInterface
{
    public function synthesize(Translation $translation, Voice $voice): AudioArtifact
    {
        $duration = max(1.0, strlen($translation->text()) / 25.0);

        return AudioArtifact::create(
            AudioId::generate(),
            $translation->translationId(),
            TextToSpeechProvider::Mock,
            $voice,
            $duration,
            FileFormat::Wav,
            '/tmp/mock-audio.wav',
            $translation->targetLanguage(),
        );
    }
}
