<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\ShadowBrowser;

use App\Application\ShadowBrowser\PlatformDetectionEngine;
use App\Domain\ShadowBrowser\BrowserPlatform;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PlatformDetectionEngineTest extends TestCase
{
    private PlatformDetectionEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new PlatformDetectionEngine();
    }

    #[DataProvider('platformUrlProvider')]
    public function testDetectsKnownPlatforms(string $url, BrowserPlatform $expected): void
    {
        self::assertSame($expected, $this->engine->detect($url));
    }

    public function testExtractHostStripsWwwPrefix(): void
    {
        self::assertSame('youtube.com', $this->engine->extractHost('https://www.youtube.com/watch?v=abc'));
    }

    public function testUnknownUrlReturnsUnknownPlatform(): void
    {
        self::assertSame(BrowserPlatform::Unknown, $this->engine->detect('https://example.com/page'));
    }

    /** @return iterable<string, array{string, BrowserPlatform}> */
    public static function platformUrlProvider(): iterable
    {
        yield 'youtube watch' => ['https://www.youtube.com/watch?v=abc', BrowserPlatform::Youtube];
        yield 'youtu.be short' => ['https://youtu.be/abc123', BrowserPlatform::Youtube];
        yield 'wikipedia' => ['https://en.wikipedia.org/wiki/PHP', BrowserPlatform::Wikipedia];
        yield 'mdn' => ['https://developer.mozilla.org/en-US/docs/Web', BrowserPlatform::Mdn];
        yield 'symfony docs' => ['https://symfony.com/doc/current/index.html', BrowserPlatform::SymfonyDocs];
        yield 'php docs' => ['https://www.php.net/manual/en/index.php', BrowserPlatform::PhpDocs];
        yield 'github' => ['https://github.com/symfony/symfony', BrowserPlatform::Github];
        yield 'gitlab' => ['https://gitlab.com/group/project', BrowserPlatform::Gitlab];
        yield 'stackoverflow' => ['https://stackoverflow.com/questions/123', BrowserPlatform::Stackoverflow];
        yield 'reddit' => ['https://www.reddit.com/r/php/', BrowserPlatform::Reddit];
        yield 'pdf viewer' => ['https://example.com/docs/guide.pdf', BrowserPlatform::PdfViewer];
    }
}
