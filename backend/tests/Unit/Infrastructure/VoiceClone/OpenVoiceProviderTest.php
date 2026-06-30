<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\VoiceClone;

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
use App\Domain\VoiceClone\VoiceCloneProvider;
use App\Infrastructure\VoiceClone\Exception\OpenVoiceProviderException;
use App\Infrastructure\VoiceClone\FixedOpenVoiceProcessRunner;
use App\Infrastructure\VoiceClone\OpenVoiceProvider;
use App\Infrastructure\VoiceClone\VoiceCloneMapper;
use App\Infrastructure\VoiceClone\VoiceCloneProcessingContext;
use PHPUnit\Framework\TestCase;

final class OpenVoiceProviderTest extends TestCase
{
    private string $outputDirectory;

    private VoiceCloneProcessingContext $processingContext;

    protected function setUp(): void
    {
        $this->outputDirectory = sys_get_temp_dir().'/history-ai-voice-clone-'.uniqid('', true);
        mkdir($this->outputDirectory);
        $this->processingContext = new VoiceCloneProcessingContext();
    }

    protected function tearDown(): void
    {
        if (is_dir($this->outputDirectory)) {
            foreach (glob($this->outputDirectory.'/*') ?: [] as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($this->outputDirectory);
        }
    }

    public function testCloneVoiceReturnsVoiceCloneArtifact(): void
    {
        $provider = new OpenVoiceProvider(
            new FixedOpenVoiceProcessRunner(),
            new VoiceCloneMapper(),
            $this->processingContext,
            'openvoice',
            'openvoice_v2',
            '/models/openvoice',
            $this->outputDirectory,
        );

        $source = $this->createSourceAudio();
        $translation = $this->createTranslation();

        $artifact = $this->processingContext->withReference(
            '/tmp/reference.wav',
            static fn () => $provider->cloneVoice($source, $translation),
        );

        self::assertSame(VoiceCloneProvider::OpenVoice, $artifact->provider());
        self::assertSame(TranslationLanguage::English, $artifact->profile()->language());
        self::assertGreaterThan(0, $artifact->profile()->duration());
        self::assertSame(44100, $artifact->profile()->sampleRate());
    }

    public function testMissingReferencePathThrows(): void
    {
        $provider = new OpenVoiceProvider(
            new FixedOpenVoiceProcessRunner(),
            new VoiceCloneMapper(),
            new VoiceCloneProcessingContext(),
            'openvoice',
            'openvoice_v2',
            '/models/openvoice',
            $this->outputDirectory,
        );

        $this->expectException(OpenVoiceProviderException::class);

        $provider->cloneVoice($this->createSourceAudio(), $this->createTranslation());
    }

    private function createSourceAudio(): AudioArtifact
    {
        return AudioArtifact::create(
            new AudioId('550e8400-e29b-41d4-a716-446655440030'),
            new TranslationId('550e8400-e29b-41d4-a716-446655440020'),
            TextToSpeechProvider::F5TTS,
            Voice::create('female_01', 'Female 01', VoiceLanguage::French, VoiceGender::Female),
            4.0,
            FileFormat::Wav,
            '/tmp/generic.wav',
            TranslationLanguage::French,
        );
    }

    private function createTranslation(): Translation
    {
        return Translation::create(
            new TranslationId('550e8400-e29b-41d4-a716-446655440020'),
            TranslationLanguage::English,
            TranslationLanguage::French,
            TranslationProvider::Qwen,
            new TranslationSegmentCollection([
                TranslationSegment::create(0, 'Hello', 'Bonjour'),
            ]),
        );
    }
}
