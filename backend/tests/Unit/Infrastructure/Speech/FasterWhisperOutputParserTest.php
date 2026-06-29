<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Speech;

use App\Domain\Speech\TranscriptLanguage;
use App\Infrastructure\Speech\Exception\FasterWhisperProviderException;
use App\Infrastructure\Speech\FasterWhisperOutputParser;
use PHPUnit\Framework\TestCase;

final class FasterWhisperOutputParserTest extends TestCase
{
    private FasterWhisperOutputParser $parser;

    protected function setUp(): void
    {
        $this->parser = new FasterWhisperOutputParser();
    }

    public function testParsesSegmentsAndLanguage(): void
    {
        $transcript = $this->parser->parse(<<<'JSON'
        {
            "language": "en",
            "segments": [
                {"index": 0, "start": 0.0, "end": 1.5, "text": "Hello world"},
                {"index": 1, "start": 1.5, "end": 3.0, "text": "Second line"}
            ]
        }
        JSON);

        self::assertSame(TranscriptLanguage::English, $transcript->language());
        self::assertSame(2, $transcript->segmentCount());
        self::assertSame('Hello world Second line', $transcript->text());
        self::assertSame(3.0, $transcript->duration());
    }

    public function testSkipsEmptySegmentText(): void
    {
        $transcript = $this->parser->parse(<<<'JSON'
        {
            "language": "fr",
            "segments": [
                {"start": 0.0, "end": 1.0, "text": "Bonjour"},
                {"start": 1.0, "end": 2.0, "text": "   "}
            ]
        }
        JSON);

        self::assertSame(TranscriptLanguage::French, $transcript->language());
        self::assertSame(1, $transcript->segmentCount());
    }

    public function testRejectsInvalidJson(): void
    {
        $this->expectException(FasterWhisperProviderException::class);

        $this->parser->parse('not-json');
    }
}
