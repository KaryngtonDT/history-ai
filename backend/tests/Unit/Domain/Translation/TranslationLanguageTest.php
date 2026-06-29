<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Translation;

use App\Domain\Translation\TranslationLanguage;
use PHPUnit\Framework\TestCase;

final class TranslationLanguageTest extends TestCase
{
    public function testSupportedLanguages(): void
    {
        self::assertSame('english', TranslationLanguage::English->value);
        self::assertSame('french', TranslationLanguage::French->value);
        self::assertSame('german', TranslationLanguage::German->value);
        self::assertSame('spanish', TranslationLanguage::Spanish->value);
        self::assertSame('italian', TranslationLanguage::Italian->value);
        self::assertSame('unknown', TranslationLanguage::Unknown->value);
    }

    public function testTryFromCode(): void
    {
        self::assertSame(TranslationLanguage::French, TranslationLanguage::tryFrom('french'));
        self::assertNull(TranslationLanguage::tryFrom('portuguese'));
    }
}
