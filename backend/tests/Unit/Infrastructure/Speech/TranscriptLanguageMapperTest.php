<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Speech;

use App\Domain\Speech\TranscriptLanguage;
use App\Infrastructure\Speech\TranscriptLanguageMapper;
use PHPUnit\Framework\TestCase;

final class TranscriptLanguageMapperTest extends TestCase
{
    public function testMapsProviderCodes(): void
    {
        self::assertSame(TranscriptLanguage::English, TranscriptLanguageMapper::fromProviderCode('en'));
        self::assertSame(TranscriptLanguage::French, TranscriptLanguageMapper::fromProviderCode('fr'));
        self::assertSame(TranscriptLanguage::German, TranscriptLanguageMapper::fromProviderCode('de'));
        self::assertSame(TranscriptLanguage::Unknown, TranscriptLanguageMapper::fromProviderCode('es'));
    }
}
