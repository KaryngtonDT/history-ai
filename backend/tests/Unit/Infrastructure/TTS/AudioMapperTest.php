<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\TTS;

use App\Domain\Translation\Translation;
use App\Domain\Translation\TranslationId;
use App\Domain\Translation\TranslationLanguage;
use App\Domain\Translation\TranslationProvider;
use App\Domain\Translation\TranslationSegment;
use App\Domain\Translation\TranslationSegmentCollection;
use App\Domain\TTS\AudioId;
use App\Domain\TTS\FileFormat;
use App\Domain\TTS\TextToSpeechProvider;
use App\Domain\TTS\Voice;
use App\Domain\TTS\VoiceGender;
use App\Domain\TTS\VoiceLanguage;
use App\Infrastructure\TTS\AudioMapper;
use App\Infrastructure\TTS\Exception\F5TextToSpeechProviderException;
use PHPUnit\Framework\TestCase;

final class AudioMapperTest extends TestCase
{
    private AudioMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new AudioMapper();
    }

    public function testMapsProcessOutputToArtifact(): void
    {
        $voice = Voice::create('female_01', 'Female 01', VoiceLanguage::French, VoiceGender::Female);
        $artifact = $this->mapper->toArtifact(
            '{"duration": 201.5, "format": "wav"}',
            new TranslationId('550e8400-e29b-41d4-a716-446655440020'),
            TextToSpeechProvider::F5TTS,
            $voice,
            new AudioId('550e8400-e29b-41d4-a716-446655440030'),
            '/tmp/test.wav',
            TranslationLanguage::French,
        );

        self::assertSame(201.5, $artifact->duration());
        self::assertSame(FileFormat::Wav, $artifact->format());
        self::assertSame(TextToSpeechProvider::F5TTS, $artifact->provider());
        self::assertSame($voice, $artifact->voice());
    }

    public function testInvalidJsonThrows(): void
    {
        $this->expectException(F5TextToSpeechProviderException::class);

        $this->mapper->toArtifact(
            'not-json',
            new TranslationId('550e8400-e29b-41d4-a716-446655440020'),
            TextToSpeechProvider::F5TTS,
            Voice::create('female_01', 'Female 01', VoiceLanguage::French, VoiceGender::Female),
            new AudioId('550e8400-e29b-41d4-a716-446655440030'),
            '/tmp/test.wav',
            TranslationLanguage::French,
        );
    }

    public function testTranslationIdFromTranslation(): void
    {
        $translation = Translation::create(
            new TranslationId('550e8400-e29b-41d4-a716-446655440020'),
            TranslationLanguage::English,
            TranslationLanguage::French,
            TranslationProvider::Qwen,
            TranslationSegmentCollection::empty(),
        );

        self::assertTrue(
            $translation->translationId()->equals($this->mapper->translationIdFrom($translation)),
        );
    }
}
