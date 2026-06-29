<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Translation;

use App\Domain\Translation\Exception\InvalidTranslationException;
use App\Domain\Translation\TranslationSegment;
use PHPUnit\Framework\TestCase;

final class TranslationSegmentTest extends TestCase
{
    public function testCreateExposesFields(): void
    {
        $segment = TranslationSegment::create(0, '  Hello world  ', '  Bonjour le monde  ');

        self::assertSame(0, $segment->index());
        self::assertSame('Hello world', $segment->sourceText());
        self::assertSame('Bonjour le monde', $segment->translatedText());
    }

    public function testRejectsEmptyTranslatedText(): void
    {
        $this->expectException(InvalidTranslationException::class);

        TranslationSegment::create(0, 'Hello', '   ');
    }

    public function testRejectsNegativeIndex(): void
    {
        $this->expectException(InvalidTranslationException::class);

        new TranslationSegment(-1, 'Hello', 'Bonjour');
    }
}
