<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Shadow;

use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Shadow\ShadowVoiceLanguage;
use App\Domain\Shadow\ShadowVoiceMode;
use App\Domain\Shadow\ShadowVoicePreference;
use PHPUnit\Framework\TestCase;

final class ShadowVoicePreferenceTest extends TestCase
{
    public function testDefaultUsesTargetLanguageMode(): void
    {
        $preference = ShadowVoicePreference::default();

        self::assertSame(ShadowVoiceMode::SameAsTargetLanguage, $preference->mode());
        self::assertNull($preference->manualLanguage());
    }

    public function testResolvesFrenchFromTargetLanguage(): void
    {
        $preference = ShadowVoicePreference::default();

        self::assertSame(
            ShadowVoiceLanguage::French,
            $preference->resolve('fr'),
        );
    }

    public function testResolvesGermanFromTargetLanguage(): void
    {
        $preference = ShadowVoicePreference::default();

        self::assertSame(
            ShadowVoiceLanguage::German,
            $preference->resolve('deutsch'),
        );
    }

    public function testFallsBackToEnglishForUnknownTargetLanguage(): void
    {
        $preference = ShadowVoicePreference::default();

        self::assertSame(
            ShadowVoiceLanguage::English,
            $preference->resolve('italian'),
        );
    }

    public function testManualModeRequiresExplicitLanguage(): void
    {
        $this->expectException(InvalidShadowSessionException::class);

        new ShadowVoicePreference(ShadowVoiceMode::Manual);
    }

    public function testManualModeUsesSelectedLanguage(): void
    {
        $preference = new ShadowVoicePreference(
            ShadowVoiceMode::Manual,
            ShadowVoiceLanguage::French,
        );

        self::assertSame(
            ShadowVoiceLanguage::French,
            $preference->resolve('en'),
        );
    }

    public function testSameAsInterfaceUsesInterfaceLanguage(): void
    {
        $preference = new ShadowVoicePreference(ShadowVoiceMode::SameAsInterface);

        self::assertSame(
            ShadowVoiceLanguage::German,
            $preference->resolve('fr', ShadowVoiceLanguage::German),
        );
    }
}
