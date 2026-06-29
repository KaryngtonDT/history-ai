<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Translation;

use App\Domain\Speech\Transcript;
use App\Domain\Speech\TranscriptId;
use App\Domain\Speech\TranscriptLanguage;
use App\Domain\Speech\TranscriptSegment;
use App\Domain\Speech\TranscriptSegmentCollection;
use App\Domain\Translation\TranslationLanguage;
use App\Domain\Translation\TranslationProvider;
use App\Infrastructure\Translation\FixedOllamaClient;
use App\Infrastructure\Translation\OllamaTranslationPromptBuilder;
use App\Infrastructure\Translation\OllamaTranslationProvider;
use PHPUnit\Framework\TestCase;

final class OllamaTranslationProviderTest extends TestCase
{
    public function testTranslateInvokesOllamaAndMapsTranslation(): void
    {
        $transcript = Transcript::create(
            new TranscriptId('550e8400-e29b-41d4-a716-446655440010'),
            TranscriptLanguage::English,
            new TranscriptSegmentCollection([
                TranscriptSegment::create(0, 0.0, 2.0, 'Hello world'),
            ]),
        );

        $provider = new OllamaTranslationProvider(
            new FixedOllamaClient(),
            new OllamaTranslationPromptBuilder(),
            'qwen3',
        );

        $translation = $provider->translate($transcript, TranslationLanguage::French);

        self::assertSame(TranslationLanguage::English, $translation->sourceLanguage());
        self::assertSame(TranslationLanguage::French, $translation->targetLanguage());
        self::assertSame(TranslationProvider::Qwen, $translation->provider());
        self::assertSame('Bonjour le monde', $translation->text());
        self::assertSame(1, $translation->segmentCount());
    }
}
