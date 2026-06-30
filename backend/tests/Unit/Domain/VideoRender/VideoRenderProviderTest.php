<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\VideoRender;

use App\Domain\VideoRender\VideoRenderProvider;
use PHPUnit\Framework\TestCase;

final class VideoRenderProviderTest extends TestCase
{
    public function testContainsExpectedProviders(): void
    {
        self::assertSame('ffmpeg', VideoRenderProvider::FFmpeg->value);
        self::assertSame('mock', VideoRenderProvider::Mock->value);
    }

    public function testCanBeResolvedFromValue(): void
    {
        self::assertSame(
            VideoRenderProvider::FFmpeg,
            VideoRenderProvider::from('ffmpeg'),
        );
    }
}
