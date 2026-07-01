<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\YouTube;

use App\Domain\Video\VideoId;
use App\Domain\YouTube\Exception\InvalidYouTubeException;
use App\Domain\YouTube\Exception\InvalidYouTubeVideoIdException;
use App\Domain\YouTube\YouTubeMetadata;
use App\Domain\YouTube\YouTubeUrl;
use App\Domain\YouTube\YouTubeVideo;
use App\Domain\YouTube\YouTubeVideoId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class YouTubeDomainTest extends TestCase
{
    #[DataProvider('validUrlsProvider')]
    public function testAcceptsSupportedYouTubeUrls(string $url): void
    {
        self::assertTrue(YouTubeUrl::isValid($url));
        new YouTubeUrl($url);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function validUrlsProvider(): iterable
    {
        yield 'watch' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ'];
        yield 'short' => ['https://youtu.be/dQw4w9WgXcQ'];
        yield 'shorts' => ['https://www.youtube.com/shorts/dQw4w9WgXcQ'];
    }

    public function testRejectsInvalidUrl(): void
    {
        $this->expectException(InvalidYouTubeException::class);
        new YouTubeUrl('https://example.com/video');
    }

    public function testCreateLinksVideoId(): void
    {
        $youtubeId = YouTubeVideoId::generate();
        $videoId = VideoId::generate();
        $metadata = new YouTubeMetadata('Lecture', 120, 'https://img.test/thumb.jpg', 'en');

        $video = YouTubeVideo::create(
            $youtubeId,
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            $metadata,
            $videoId,
        );

        self::assertTrue($video->id()->equals($youtubeId));
        self::assertTrue($video->videoId()->equals($videoId));
        self::assertSame('Lecture', $video->metadata()->title);
    }

    public function testRejectsInvalidVideoId(): void
    {
        $this->expectException(InvalidYouTubeVideoIdException::class);
        new YouTubeVideoId('not-valid');
    }
}
