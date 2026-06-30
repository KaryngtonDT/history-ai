<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\VoiceClone;

use App\Domain\Translation\TranslationLanguage;
use App\Domain\VoiceClone\Exception\InvalidVoiceCloneException;
use App\Domain\VoiceClone\VoiceProfile;
use App\Domain\VoiceClone\VoiceProfileId;
use PHPUnit\Framework\TestCase;

final class VoiceProfileTest extends TestCase
{
    private const string PROFILE_ID = '550e8400-e29b-41d4-a716-446655440040';

    public function testCreateExposesFields(): void
    {
        $profile = VoiceProfile::create(
            new VoiceProfileId(self::PROFILE_ID),
            TranslationLanguage::English,
            12.5,
            44100,
        );

        self::assertTrue($profile->profileId()->equals(new VoiceProfileId(self::PROFILE_ID)));
        self::assertSame(TranslationLanguage::English, $profile->language());
        self::assertSame(12.5, $profile->duration());
        self::assertSame(44100, $profile->sampleRate());
    }

    public function testNegativeDurationThrows(): void
    {
        $this->expectException(InvalidVoiceCloneException::class);

        VoiceProfile::create(
            new VoiceProfileId(self::PROFILE_ID),
            TranslationLanguage::French,
            -1.0,
            44100,
        );
    }

    public function testInvalidSampleRateThrows(): void
    {
        $this->expectException(InvalidVoiceCloneException::class);

        VoiceProfile::create(
            new VoiceProfileId(self::PROFILE_ID),
            TranslationLanguage::French,
            1.0,
            0,
        );
    }
}
