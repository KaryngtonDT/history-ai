<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\ShadowBrowser;

use App\Application\ShadowBrowser\BrowserYouTubeUrlParser;
use PHPUnit\Framework\TestCase;

final class BrowserYouTubeUrlParserTest extends TestCase
{
    private BrowserYouTubeUrlParser $parser;

    protected function setUp(): void
    {
        $this->parser = new BrowserYouTubeUrlParser();
    }

    public function testExtractsWatchVideoKey(): void
    {
        self::assertSame(
            'dQw4w9WgXcQ',
            $this->parser->extractVideoKey('https://www.youtube.com/watch?v=dQw4w9WgXcQ'),
        );
    }

    public function testMatchesEquivalentYoutubeUrls(): void
    {
        self::assertTrue($this->parser->urlsMatch(
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'https://youtu.be/dQw4w9WgXcQ',
        ));
    }
}
