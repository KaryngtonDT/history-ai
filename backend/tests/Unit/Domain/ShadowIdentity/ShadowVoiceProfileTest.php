<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ShadowIdentity;

use App\Domain\ShadowIdentity\Exception\InvalidShadowIdentityException;
use App\Domain\ShadowIdentity\ShadowHumorLevel;
use App\Domain\ShadowIdentity\ShadowVoiceProfile;
use PHPUnit\Framework\TestCase;

final class ShadowVoiceProfileTest extends TestCase
{
    public function testDefaultProfileIsValid(): void
    {
        $profile = ShadowVoiceProfile::default();

        self::assertSame('browser-default', $profile->voiceId());
        self::assertSame('browser_tts', $profile->engine());
        self::assertSame(1.0, $profile->speed());
    }

    public function testSpeedCanBeAdjusted(): void
    {
        $slower = ShadowVoiceProfile::default()->withSpeed(0.9);

        self::assertSame(1.0, ShadowVoiceProfile::default()->speed());
        self::assertSame(0.9, $slower->speed());
    }

    public function testRejectsInvalidSpeed(): void
    {
        $this->expectException(InvalidShadowIdentityException::class);

        ShadowVoiceProfile::default()->withSpeed(3.0);
    }

    public function testHumorCanBeUpdated(): void
    {
        $profile = ShadowVoiceProfile::default()->withHumor(ShadowHumorLevel::High);

        self::assertSame(ShadowHumorLevel::High, $profile->humor());
    }
}
