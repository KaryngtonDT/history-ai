<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Translation;

use App\Domain\Speech\TranscriptLanguage;
use App\Domain\Translation\TranslationLanguage;
use App\Infrastructure\Translation\TranscriptLanguageMapper;
use PHPUnit\Framework\TestCase;

final class TranscriptLanguageMapperTest extends TestCase
{
    public function testMapsKnownLanguages(): void
    {
        self::assertSame(
            TranslationLanguage::English,
            TranscriptLanguageMapper::toTranslationLanguage(TranscriptLanguage::English),
        );
        self::assertSame(
            TranslationLanguage::French,
            TranscriptLanguageMapper::toTranslationLanguage(TranscriptLanguage::French),
        );
        self::assertSame(
            TranslationLanguage::Unknown,
            TranscriptLanguageMapper::toTranslationLanguage(TranscriptLanguage::Unknown),
        );
    }
}
