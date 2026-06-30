<?php

declare(strict_types=1);

namespace App\Infrastructure\VideoRender;

use App\Domain\VideoRender\VideoRenderProvider;
use App\Domain\VideoRender\VideoRenderProviderInterface;
use App\Infrastructure\VideoRender\Exception\InvalidVideoRenderConfigurationException;

final class VideoRenderProviderFactory
{
    public const string PROVIDER_FFMPEG = 'ffmpeg';

    public function __construct(
        private readonly string $defaultProviderName,
        private readonly FFmpegVideoRenderProvider $ffmpegVideoRenderProvider,
        private readonly MockVideoRenderProvider $mockVideoRenderProvider,
    ) {
    }

    public function resolve(?VideoRenderProvider $provider = null): VideoRenderProviderInterface
    {
        if (null !== $provider) {
            return match ($provider) {
                VideoRenderProvider::FFmpeg => $this->ffmpegVideoRenderProvider,
                VideoRenderProvider::Mock => $this->mockVideoRenderProvider,
            };
        }

        $normalized = strtolower(trim($this->defaultProviderName));

        if ('' === $normalized || self::PROVIDER_FFMPEG === $normalized) {
            return $this->ffmpegVideoRenderProvider;
        }

        if ('mock' === $normalized) {
            return $this->mockVideoRenderProvider;
        }

        throw new InvalidVideoRenderConfigurationException(sprintf(
            'Unknown VIDEO_RENDER_PROVIDER value "%s". Supported values: ffmpeg, mock.',
            $this->defaultProviderName,
        ));
    }
}
