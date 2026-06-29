<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Translation;

use App\Domain\Speech\Transcript;
use App\Domain\Speech\TranscriptId;
use App\Domain\Speech\TranscriptLanguage;
use App\Domain\Speech\TranscriptSegment;
use App\Domain\Speech\TranscriptSegmentCollection;
use App\Domain\Translation\TranslationLanguage;
use App\Infrastructure\Translation\OllamaTranslationPromptBuilder;
use PHPUnit\Framework\TestCase;

final class OllamaTranslationPromptBuilderTest extends TestCase
{
    public function testBuildIncludesLanguagesAndSegments(): void
    {
        $transcript = Transcript::create(
            new TranscriptId('550e8400-e29b-41d4-a716-446655440010'),
            TranscriptLanguage::English,
            new TranscriptSegmentCollection([
                TranscriptSegment::create(0, 0.0, 2.0, 'Hello world'),
            ]),
        );

        $builder = new OllamaTranslationPromptBuilder();
        $prompt = $builder->build($transcript, TranslationLanguage::French);

        self::assertStringContainsString('english', $prompt);
        self::assertStringContainsString('french', $prompt);
        self::assertStringContainsString('0: Hello world', $prompt);
        self::assertStringContainsString('Do not summarize', $prompt);
    }

    public function testMapResponseToSegments(): void
    {
        $transcript = Transcript::create(
            new TranscriptId('550e8400-e29b-41d4-a716-446655440010'),
            TranscriptLanguage::English,
            new TranscriptSegmentCollection([
                TranscriptSegment::create(0, 0.0, 2.0, 'Hello'),
                TranscriptSegment::create(1, 2.0, 4.0, 'world'),
            ]),
        );

        $builder = new OllamaTranslationPromptBuilder();
        $mapped = $builder->mapResponseToSegments(
            $transcript,
            '{"segments":[{"index":0,"translatedText":"Bonjour"},{"index":1,"translatedText":"le monde"}]}',
        );

        self::assertSame([
            ['index' => 0, 'sourceText' => 'Hello', 'translatedText' => 'Bonjour'],
            ['index' => 1, 'sourceText' => 'world', 'translatedText' => 'le monde'],
        ], $mapped);
    }
}
