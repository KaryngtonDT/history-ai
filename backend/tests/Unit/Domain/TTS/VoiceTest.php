<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\TTS;

use App\Domain\TTS\Exception\InvalidAudioArtifactException;
use App\Domain\TTS\Voice;
use App\Domain\TTS\VoiceGender;
use App\Domain\TTS\VoiceLanguage;
use PHPUnit\Framework\TestCase;

final class VoiceTest extends TestCase
{
    public function testCreateExposesFields(): void
    {
        $voice = Voice::create('female_01', 'Female 01', VoiceLanguage::French, VoiceGender::Female);

        self::assertSame('female_01', $voice->voiceId());
        self::assertSame('Female 01', $voice->displayName());
        self::assertSame(VoiceLanguage::French, $voice->language());
        self::assertSame(VoiceGender::Female, $voice->gender());
    }

    public function testEmptyVoiceIdThrows(): void
    {
        $this->expectException(InvalidAudioArtifactException::class);

        Voice::create('  ', 'Female 01', VoiceLanguage::French, VoiceGender::Female);
    }

    public function testEmptyDisplayNameThrows(): void
    {
        $this->expectException(InvalidAudioArtifactException::class);

        Voice::create('female_01', '', VoiceLanguage::French, VoiceGender::Female);
    }
}
