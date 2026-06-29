<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Translation;

use App\Domain\Speech\Transcript;
use App\Domain\Speech\TranscriptId;
use App\Domain\Speech\TranscriptLanguage;
use App\Domain\Speech\TranscriptSegment;
use App\Domain\Speech\TranscriptSegmentCollection;
use App\Domain\Translation\Translation;
use App\Domain\Translation\TranslationId;
use App\Domain\Translation\TranslationLanguage;
use App\Domain\Translation\TranslationProvider;
use App\Domain\Translation\TranslationProviderInterface;
use App\Domain\Translation\TranslationSegment;
use App\Domain\Translation\TranslationSegmentCollection;
use PHPUnit\Framework\TestCase;

final class TranslationProviderInterfaceTest extends TestCase
{
    public function testProviderInterfaceDefinesTranslateMethod(): void
    {
        $transcript = Transcript::create(
            new TranscriptId('550e8400-e29b-41d4-a716-446655440010'),
            TranscriptLanguage::English,
            new TranscriptSegmentCollection([
                TranscriptSegment::create(0, 0.0, 2.0, 'Hello world'),
            ]),
        );

        $expected = Translation::create(
            new TranslationId('550e8400-e29b-41d4-a716-446655440020'),
            TranslationLanguage::English,
            TranslationLanguage::French,
            TranslationProvider::Qwen,
            new TranslationSegmentCollection([
                TranslationSegment::create(0, 'Hello world', 'Bonjour le monde'),
            ]),
        );

        $provider = $this->createMock(TranslationProviderInterface::class);
        $provider
            ->expects(self::once())
            ->method('translate')
            ->with($transcript, TranslationLanguage::French)
            ->willReturn($expected);

        self::assertSame($expected, $provider->translate($transcript, TranslationLanguage::French));
    }
}
