<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Shadow;

use App\Application\Shadow\ShadowAnswerLanguageResolver;
use App\Domain\Shadow\ShadowVoiceLanguage;
use App\Domain\Shadow\ShadowVoiceMode;
use App\Domain\Shadow\ShadowVoicePreference;
use PHPUnit\Framework\TestCase;

final class ShadowAnswerLanguageResolverTest extends TestCase
{
    private ShadowAnswerLanguageResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new ShadowAnswerLanguageResolver();
    }

    public function testResolvesFrenchFromTargetLanguage(): void
    {
        $voice = $this->resolver->resolve(
            'Explain this sentence.',
            'fr',
            ShadowVoicePreference::default(),
        );

        self::assertSame(ShadowVoiceLanguage::French, $voice->answerLanguage);
        self::assertSame('target_language', $voice->reason);
        self::assertFalse($voice->fallbackUsed);
    }

    public function testResolvesEnglishFromTargetLanguage(): void
    {
        $voice = $this->resolver->resolve(
            'Explain this sentence.',
            'english',
            ShadowVoicePreference::default(),
        );

        self::assertSame(ShadowVoiceLanguage::English, $voice->answerLanguage);
        self::assertSame('target_language', $voice->reason);
    }

    public function testResolvesGermanFromTargetLanguage(): void
    {
        $voice = $this->resolver->resolve(
            'Explain this sentence.',
            'de',
            ShadowVoicePreference::default(),
        );

        self::assertSame(ShadowVoiceLanguage::German, $voice->answerLanguage);
        self::assertSame('target_language', $voice->reason);
    }

    public function testExplicitFrenchOverride(): void
    {
        $voice = $this->resolver->resolve(
            'Please explique en français what this means.',
            'english',
            ShadowVoicePreference::default(),
        );

        self::assertSame(ShadowVoiceLanguage::French, $voice->answerLanguage);
        self::assertSame('explicit_user_override', $voice->reason);
    }

    public function testExplicitEnglishOverride(): void
    {
        $voice = $this->resolver->resolve(
            'answer in English please',
            'fr',
            ShadowVoicePreference::default(),
        );

        self::assertSame(ShadowVoiceLanguage::English, $voice->answerLanguage);
        self::assertSame('explicit_user_override', $voice->reason);
    }

    public function testExplicitGermanOverride(): void
    {
        $voice = $this->resolver->resolve(
            'Bitte auf Deutsch erklären',
            'english',
            ShadowVoicePreference::default(),
        );

        self::assertSame(ShadowVoiceLanguage::German, $voice->answerLanguage);
        self::assertSame('explicit_user_override', $voice->reason);
    }

    public function testFallsBackToEnglishForUnknownTargetLanguage(): void
    {
        $voice = $this->resolver->resolve(
            'Explain this.',
            'italian',
            ShadowVoicePreference::default(),
        );

        self::assertSame(ShadowVoiceLanguage::English, $voice->answerLanguage);
        self::assertTrue($voice->fallbackUsed);
        self::assertSame('target_language_fallback', $voice->reason);
    }

    public function testManualPreferenceUsesSelectedLanguage(): void
    {
        $voice = $this->resolver->resolve(
            'Explain this.',
            'fr',
            new ShadowVoicePreference(ShadowVoiceMode::Manual, ShadowVoiceLanguage::German),
        );

        self::assertSame(ShadowVoiceLanguage::German, $voice->answerLanguage);
        self::assertSame('manual_selection', $voice->reason);
    }
}
