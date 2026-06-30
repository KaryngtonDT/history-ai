<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\VideoRender;

use App\Domain\VideoRender\VideoRenderProvider;
use App\Domain\VideoRender\VideoRenderProviderInterface;
use App\Infrastructure\VideoRender\Exception\InvalidVideoRenderConfigurationException;
use App\Infrastructure\VideoRender\FFmpegVideoRenderProvider;
use App\Infrastructure\VideoRender\FixedFFmpegProcessRunner;
use App\Infrastructure\VideoRender\MockVideoRenderProvider;
use App\Infrastructure\VideoRender\VideoRenderMapper;
use App\Infrastructure\VideoRender\VideoRenderProviderFactory;
use PHPUnit\Framework\TestCase;

final class VideoRenderProviderFactoryTest extends TestCase
{
    private VideoRenderProviderFactory $factory;

    protected function setUp(): void
    {
        $outputDirectory = sys_get_temp_dir().'/history-ai-render-factory';

        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory);
        }

        $this->factory = new VideoRenderProviderFactory(
            'ffmpeg',
            new FFmpegVideoRenderProvider(
                new FixedFFmpegProcessRunner(),
                new VideoRenderMapper(),
                'ffmpeg',
                $outputDirectory,
            ),
            new MockVideoRenderProvider(),
        );
    }

    public function testResolveDefaultReturnsFfmpeg(): void
    {
        self::assertInstanceOf(FFmpegVideoRenderProvider::class, $this->factory->resolve());
    }

    public function testResolveExplicitFfmpeg(): void
    {
        self::assertInstanceOf(
            FFmpegVideoRenderProvider::class,
            $this->factory->resolve(VideoRenderProvider::FFmpeg),
        );
    }

    public function testResolveMock(): void
    {
        self::assertInstanceOf(
            MockVideoRenderProvider::class,
            $this->factory->resolve(VideoRenderProvider::Mock),
        );
    }

    public function testUnknownDefaultProviderThrows(): void
    {
        $outputDirectory = sys_get_temp_dir().'/history-ai-render-factory-unknown';

        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory);
        }

        $factory = new VideoRenderProviderFactory(
            'unknown',
            new FFmpegVideoRenderProvider(
                new FixedFFmpegProcessRunner(),
                new VideoRenderMapper(),
                'ffmpeg',
                $outputDirectory,
            ),
            new MockVideoRenderProvider(),
        );

        $this->expectException(InvalidVideoRenderConfigurationException::class);

        $factory->resolve();
    }

    public function testResolvedProviderImplementsInterface(): void
    {
        self::assertInstanceOf(VideoRenderProviderInterface::class, $this->factory->resolve());
    }
}
