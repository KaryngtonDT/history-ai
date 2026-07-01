<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Shadow;

use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Shadow\ShadowVoiceLanguage;
use PHPUnit\Framework\TestCase;

final class ShadowVoiceLanguageTest extends TestCase
{
    public function testMapsTargetLanguageAliases(): void
    {
        self::assertSame(ShadowVoiceLanguage::English, ShadowVoiceLanguage::tryFromTargetLanguage('english'));
        self::assertSame(ShadowVoiceLanguage::French, ShadowVoiceLanguage::tryFromTargetLanguage('français'));
        self::assertSame(ShadowVoiceLanguage::German, ShadowVoiceLanguage::tryFromTargetLanguage('de'));
    }

    public function testFallbackIsEnglish(): void
    {
        self::assertSame(ShadowVoiceLanguage::English, ShadowVoiceLanguage::fallback());
    }

    public function testProvidesBrowserLocaleCodes(): void
    {
        self::assertSame('fr-FR', ShadowVoiceLanguage::French->bcp47());
        self::assertSame('de-DE', ShadowVoiceLanguage::German->bcp47());
    }

    public function testRejectsUnsupportedLanguage(): void
    {
        $this->expectException(InvalidShadowSessionException::class);

        ShadowVoiceLanguage::fromString('italian');
    }
}
