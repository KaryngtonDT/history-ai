<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\VoiceClone;

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
use App\Domain\TTS\Voice;
use App\Domain\TTS\VoiceGender;
use App\Domain\TTS\VoiceLanguage;
use App\Domain\VoiceClone\VoiceCloneArtifact;
use App\Domain\VoiceClone\VoiceCloneArtifactId;
use App\Domain\VoiceClone\VoiceCloneProvider;
use App\Domain\VoiceClone\VoiceCloneProviderInterface;
use App\Domain\VoiceClone\VoiceProfile;
use App\Domain\VoiceClone\VoiceProfileId;
use PHPUnit\Framework\TestCase;

final class VoiceCloneProviderInterfaceTest extends TestCase
{
    public function testProviderInterfaceDefinesCloneVoiceMethod(): void
    {
        $source = AudioArtifact::create(
            new AudioId('550e8400-e29b-41d4-a716-446655440030'),
            new TranslationId('550e8400-e29b-41d4-a716-446655440020'),
            TextToSpeechProvider::F5TTS,
            Voice::create('female_01', 'Female 01', VoiceLanguage::French, VoiceGender::Female),
            3.5,
            FileFormat::Wav,
            '/tmp/generic.wav',
            TranslationLanguage::French,
        );

        $translation = Translation::create(
            new TranslationId('550e8400-e29b-41d4-a716-446655440020'),
            TranslationLanguage::English,
            TranslationLanguage::French,
            TranslationProvider::Qwen,
            new TranslationSegmentCollection([
                TranslationSegment::create(0, 'Hello', 'Bonjour'),
            ]),
        );

        $expected = VoiceCloneArtifact::create(
            new VoiceCloneArtifactId('550e8400-e29b-41d4-a716-446655440050'),
            VoiceProfile::create(
                new VoiceProfileId('550e8400-e29b-41d4-a716-446655440040'),
                TranslationLanguage::English,
                3.5,
                44100,
            ),
            VoiceCloneProvider::OpenVoice,
            new AudioId('550e8400-e29b-41d4-a716-446655440060'),
        );

        $provider = $this->createMock(VoiceCloneProviderInterface::class);
        $provider
            ->expects(self::once())
            ->method('cloneVoice')
            ->with($source, $translation)
            ->willReturn($expected);

        self::assertSame($expected, $provider->cloneVoice($source, $translation));
    }
}
