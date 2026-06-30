<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\TTS;

use App\Domain\Translation\Translation;
use App\Domain\Translation\TranslationId;
use App\Domain\Translation\TranslationLanguage;
use App\Domain\Translation\TranslationProvider;
use App\Domain\Translation\TranslationSegment;
use App\Domain\Translation\TranslationSegmentCollection;
use App\Domain\TTS\AudioArtifact;
use App\Domain\TTS\AudioId;
use App\Domain\TTS\FileFormat;
use App\Domain\TTS\TextToSpeechProvider;
use App\Domain\TTS\TextToSpeechProviderInterface;
use App\Domain\TTS\Voice;
use App\Domain\TTS\VoiceGender;
use App\Domain\TTS\VoiceLanguage;
use PHPUnit\Framework\TestCase;

final class TextToSpeechProviderInterfaceTest extends TestCase
{
    public function testProviderInterfaceDefinesSynthesizeMethod(): void
    {
        $translation = Translation::create(
            new TranslationId('550e8400-e29b-41d4-a716-446655440020'),
            TranslationLanguage::English,
            TranslationLanguage::French,
            TranslationProvider::Qwen,
            new TranslationSegmentCollection([
                TranslationSegment::create(0, 'Hello', 'Bonjour'),
            ]),
        );

        $voice = Voice::create('female_01', 'Female 01', VoiceLanguage::French, VoiceGender::Female);

        $expected = AudioArtifact::create(
            new AudioId('550e8400-e29b-41d4-a716-446655440030'),
            $translation->translationId(),
            TextToSpeechProvider::F5TTS,
            $voice,
            3.5,
            FileFormat::Wav,
        );

        $provider = $this->createMock(TextToSpeechProviderInterface::class);
        $provider
            ->expects(self::once())
            ->method('synthesize')
            ->with($translation, $voice)
            ->willReturn($expected);

        self::assertSame($expected, $provider->synthesize($translation, $voice));
    }
}
