<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\TTS;

use App\Domain\Translation\Translation;
use App\Domain\Translation\TranslationId;
use App\Domain\Translation\TranslationLanguage;
use App\Domain\Translation\TranslationProvider;
use App\Domain\Translation\TranslationSegment;
use App\Domain\Translation\TranslationSegmentCollection;
use App\Domain\TTS\FileFormat;
use App\Domain\TTS\TextToSpeechProvider;
use App\Domain\TTS\Voice;
use App\Domain\TTS\VoiceGender;
use App\Domain\TTS\VoiceLanguage;
use App\Infrastructure\TTS\AudioMapper;
use App\Infrastructure\TTS\Exception\F5TextToSpeechProviderException;
use App\Infrastructure\TTS\F5TextToSpeechProvider;
use App\Infrastructure\TTS\FixedF5ProcessRunner;
use PHPUnit\Framework\TestCase;

final class F5TextToSpeechProviderTest extends TestCase
{
    private string $outputDirectory;

    protected function setUp(): void
    {
        $this->outputDirectory = sys_get_temp_dir().'/history-ai-tts-'.uniqid('', true);
        mkdir($this->outputDirectory);
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

    public function testSynthesizeReturnsAudioArtifact(): void
    {
        $provider = new F5TextToSpeechProvider(
            new FixedF5ProcessRunner(),
            new AudioMapper(),
            'f5-tts',
            'F5-TTS',
            '/models/f5',
            $this->outputDirectory,
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

        $artifact = $provider->synthesize(
            $translation,
            Voice::create('female_01', 'Female 01', VoiceLanguage::French, VoiceGender::Female),
        );

        self::assertSame(TextToSpeechProvider::F5TTS, $artifact->provider());
        self::assertSame(FileFormat::Wav, $artifact->format());
        self::assertGreaterThan(0, $artifact->duration());
        self::assertSame('female_01', $artifact->voice()->voiceId());
    }

    public function testEmptyTranslationTextThrows(): void
    {
        $provider = new F5TextToSpeechProvider(
            new FixedF5ProcessRunner(),
            new AudioMapper(),
            'f5-tts',
            'F5-TTS',
            '/models/f5',
            $this->outputDirectory,
        );

        $translation = Translation::create(
            new TranslationId('550e8400-e29b-41d4-a716-446655440020'),
            TranslationLanguage::English,
            TranslationLanguage::French,
            TranslationProvider::Qwen,
            TranslationSegmentCollection::empty(),
        );

        $this->expectException(F5TextToSpeechProviderException::class);

        $provider->synthesize(
            $translation,
            Voice::create('female_01', 'Female 01', VoiceLanguage::French, VoiceGender::Female),
        );
    }

    public function testAvailableVoicesAreNotEmpty(): void
    {
        $provider = new F5TextToSpeechProvider(
            new FixedF5ProcessRunner(),
            new AudioMapper(),
            'f5-tts',
            'F5-TTS',
            '/models/f5',
            $this->outputDirectory,
        );

        self::assertGreaterThan(0, $provider->availableVoices()->count());
    }
}
