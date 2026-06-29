<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Translation;

use App\Domain\Translation\Translation;
use App\Domain\Translation\TranslationId;
use App\Domain\Translation\TranslationLanguage;
use App\Domain\Translation\TranslationProvider;
use App\Domain\Translation\TranslationSegment;
use App\Domain\Translation\TranslationSegmentCollection;
use PHPUnit\Framework\TestCase;

final class TranslationTest extends TestCase
{
    private const string TRANSLATION_ID = '550e8400-e29b-41d4-a716-446655440020';

    public function testCreateExposesFields(): void
    {
        $segments = new TranslationSegmentCollection([
            TranslationSegment::create(0, 'Hello', 'Bonjour'),
            TranslationSegment::create(1, 'world', 'le monde'),
        ]);
        $translation = Translation::create(
            new TranslationId(self::TRANSLATION_ID),
            TranslationLanguage::English,
            TranslationLanguage::French,
            TranslationProvider::Qwen,
            $segments,
        );

        self::assertTrue($translation->translationId()->equals(new TranslationId(self::TRANSLATION_ID)));
        self::assertSame(TranslationLanguage::English, $translation->sourceLanguage());
        self::assertSame(TranslationLanguage::French, $translation->targetLanguage());
        self::assertSame(TranslationProvider::Qwen, $translation->provider());
        self::assertSame(2, $translation->segmentCount());
    }

    public function testTextJoinsTranslatedSegmentTexts(): void
    {
        $translation = Translation::create(
            new TranslationId(self::TRANSLATION_ID),
            TranslationLanguage::English,
            TranslationLanguage::German,
            TranslationProvider::Qwen,
            new TranslationSegmentCollection([
                TranslationSegment::create(0, 'Hello', 'Hallo'),
                TranslationSegment::create(1, 'world', 'Welt'),
            ]),
        );

        self::assertSame('Hallo Welt', $translation->text());
    }

    public function testEmptyTranslationReturnsEmptyText(): void
    {
        $translation = Translation::create(
            new TranslationId(self::TRANSLATION_ID),
            TranslationLanguage::Unknown,
            TranslationLanguage::French,
            TranslationProvider::Mock,
            TranslationSegmentCollection::empty(),
        );

        self::assertSame('', $translation->text());
        self::assertSame(0, $translation->segmentCount());
    }
}
