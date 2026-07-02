<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ShadowIdentity;

use App\Domain\ShadowIdentity\Exception\InvalidShadowIdentityException;
use App\Domain\ShadowIdentity\ShadowChallengeProfile;
use App\Domain\ShadowIdentity\ShadowConversationStyle;
use App\Domain\ShadowIdentity\ShadowIdentity;
use App\Domain\ShadowIdentity\ShadowIdentityPreferences;
use App\Domain\ShadowIdentity\ShadowVoicePersona;
use App\Domain\ShadowIdentity\ShadowVoiceProfile;
use PHPUnit\Framework\TestCase;

final class ShadowIdentityTest extends TestCase
{
    public function testCreatesDefaultIdentityWithHistory(): void
    {
        $identity = ShadowIdentity::create();

        self::assertSame('default', $identity->scopeKey());
        self::assertSame(ShadowVoicePersona::Teacher, $identity->preferences()->persona());
        self::assertSame(1, $identity->history()->count());
    }

    public function testApplyPreferencesAppendsHistory(): void
    {
        $identity = ShadowIdentity::create();
        $updated = $identity->withPersona(ShadowVoicePersona::Storyteller);

        self::assertSame(ShadowVoicePersona::Teacher, $identity->preferences()->persona());
        self::assertSame(ShadowVoicePersona::Storyteller, $updated->preferences()->persona());
        self::assertSame(2, $updated->history()->count());
    }

    public function testResetRestoresDefaultsAndRecordsHistory(): void
    {
        $identity = ShadowIdentity::create()
            ->withPersona(ShadowVoicePersona::Debater)
            ->withChallengeLevel(5);

        $reset = $identity->reset();

        self::assertSame(ShadowVoicePersona::Teacher, $reset->preferences()->persona());
        self::assertSame(3, $reset->preferences()->challengeProfile()->level());
        self::assertStringContainsString('reset', strtolower($reset->history()->latest()?->label() ?? ''));
    }

    public function testChallengeLevelChangeIsImmutable(): void
    {
        $identity = ShadowIdentity::create();
        $harder = $identity->withChallengeLevel(5);

        self::assertSame(3, $identity->preferences()->challengeProfile()->level());
        self::assertSame(5, $harder->preferences()->challengeProfile()->level());
    }

    public function testVoiceProfileUpdateRecordsSnapshot(): void
    {
        $voice = ShadowVoiceProfile::default()->withSpeed(0.9);
        $identity = ShadowIdentity::create()->withVoiceProfile($voice);

        self::assertSame(0.9, $identity->preferences()->voiceProfile()->speed());
        self::assertSame(2, $identity->history()->count());
    }

    public function testForgetPreferenceUpdatesMemoryPolicy(): void
    {
        $withInterest = ShadowIdentity::create()->applyPreferences(
            ShadowIdentityPreferences::default()->withMemoryPolicy(
                ShadowIdentityPreferences::default()->memoryPolicy()->withInterest('history'),
            ),
            'Added interest',
        );

        $forgotten = $withInterest->forgetPreference('history');

        self::assertNotContains('history', $forgotten->preferences()->memoryPolicy()->interests());
    }
}
