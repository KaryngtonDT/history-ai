<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\TTS;

use App\Domain\Translation\TranslationId;
use App\Domain\TTS\AudioArtifact;
use App\Domain\TTS\AudioId;
use App\Domain\TTS\Exception\InvalidAudioArtifactException;
use App\Domain\TTS\FileFormat;
use App\Domain\TTS\TextToSpeechProvider;
use App\Domain\TTS\Voice;
use App\Domain\TTS\VoiceGender;
use App\Domain\TTS\VoiceLanguage;
use PHPUnit\Framework\TestCase;

final class AudioArtifactTest extends TestCase
{
    private const string AUDIO_ID = '550e8400-e29b-41d4-a716-446655440030';
    private const string TRANSLATION_ID = '550e8400-e29b-41d4-a716-446655440020';

    public function testCreateExposesFields(): void
    {
        $voice = Voice::create('female_01', 'Female 01', VoiceLanguage::French, VoiceGender::Female);
        $artifact = AudioArtifact::create(
            new AudioId(self::AUDIO_ID),
            new TranslationId(self::TRANSLATION_ID),
            TextToSpeechProvider::F5TTS,
            $voice,
            201.5,
            FileFormat::Wav,
        );

        self::assertTrue($artifact->audioId()->equals(new AudioId(self::AUDIO_ID)));
        self::assertTrue($artifact->translationId()->equals(new TranslationId(self::TRANSLATION_ID)));
        self::assertSame(TextToSpeechProvider::F5TTS, $artifact->provider());
        self::assertSame($voice, $artifact->voice());
        self::assertSame(201.5, $artifact->duration());
        self::assertSame(FileFormat::Wav, $artifact->format());
    }

    public function testNegativeDurationThrows(): void
    {
        $this->expectException(InvalidAudioArtifactException::class);

        AudioArtifact::create(
            new AudioId(self::AUDIO_ID),
            new TranslationId(self::TRANSLATION_ID),
            TextToSpeechProvider::Mock,
            Voice::create('neutral_01', 'Neutral 01', VoiceLanguage::English, VoiceGender::Neutral),
            -1.0,
            FileFormat::Wav,
        );
    }
}
