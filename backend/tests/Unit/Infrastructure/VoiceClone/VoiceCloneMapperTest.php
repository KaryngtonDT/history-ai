<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\VoiceClone;

use App\Domain\Translation\Translation;
use App\Domain\Translation\TranslationId;
use App\Domain\Translation\TranslationLanguage;
use App\Domain\Translation\TranslationProvider;
use App\Domain\Translation\TranslationSegment;
use App\Domain\Translation\TranslationSegmentCollection;
use App\Domain\TTS\AudioId;
use App\Domain\VoiceClone\VoiceCloneArtifactId;
use App\Domain\VoiceClone\VoiceCloneProvider;
use App\Infrastructure\VoiceClone\Exception\OpenVoiceProviderException;
use App\Infrastructure\VoiceClone\VoiceCloneMapper;
use PHPUnit\Framework\TestCase;

final class VoiceCloneMapperTest extends TestCase
{
    public function testToArtifactMapsProcessOutput(): void
    {
        $mapper = new VoiceCloneMapper();
        $translation = Translation::create(
            new TranslationId('550e8400-e29b-41d4-a716-446655440020'),
            TranslationLanguage::English,
            TranslationLanguage::French,
            TranslationProvider::Qwen,
            new TranslationSegmentCollection([
                TranslationSegment::create(0, 'Hello', 'Bonjour'),
            ]),
        );

        $artifact = $mapper->toArtifact(
            json_encode(['duration' => 5.5, 'sampleRate' => 48000], JSON_THROW_ON_ERROR),
            $translation,
            VoiceCloneProvider::OpenVoice,
            new AudioId('550e8400-e29b-41d4-a716-446655440060'),
            new VoiceCloneArtifactId('550e8400-e29b-41d4-a716-446655440050'),
            new AudioId('550e8400-e29b-41d4-a716-446655440030'),
            '/tmp/cloned.wav',
        );

        self::assertSame(VoiceCloneProvider::OpenVoice, $artifact->provider());
        self::assertSame(5.5, $artifact->profile()->duration());
        self::assertSame(48000, $artifact->profile()->sampleRate());
        self::assertSame(TranslationLanguage::English, $artifact->profile()->language());
    }

    public function testInvalidJsonThrows(): void
    {
        $mapper = new VoiceCloneMapper();

        $this->expectException(OpenVoiceProviderException::class);

        $mapper->toArtifact(
            'not-json',
            Translation::create(
                new TranslationId('550e8400-e29b-41d4-a716-446655440020'),
                TranslationLanguage::English,
                TranslationLanguage::French,
                TranslationProvider::Qwen,
                TranslationSegmentCollection::empty(),
            ),
            VoiceCloneProvider::OpenVoice,
            new AudioId('550e8400-e29b-41d4-a716-446655440060'),
            new VoiceCloneArtifactId('550e8400-e29b-41d4-a716-446655440050'),
            new AudioId('550e8400-e29b-41d4-a716-446655440030'),
            '/tmp/cloned.wav',
        );
    }
}
