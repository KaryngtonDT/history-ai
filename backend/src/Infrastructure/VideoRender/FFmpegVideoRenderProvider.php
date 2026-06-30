<?php

declare(strict_types=1);

namespace App\Infrastructure\VideoRender;

use App\Domain\LipSync\LipSyncArtifact;
use App\Domain\VideoRender\FinalVideoArtifact;
use App\Domain\VideoRender\FinalVideoId;
use App\Domain\VideoRender\VideoRenderFormat;
use App\Domain\VideoRender\VideoRenderProvider;
use App\Domain\VideoRender\VideoRenderProviderInterface;
use App\Domain\VideoRender\VideoRenderQuality;
use App\Infrastructure\VideoRender\Exception\FFmpegProviderException;

final class FFmpegVideoRenderProvider implements VideoRenderProviderInterface
{
    public function __construct(
        private readonly FFmpegProcessRunner $processRunner,
        private readonly VideoRenderMapper $videoRenderMapper,
        private readonly string $binary,
        private readonly string $outputDirectory,
    ) {
    }

    public function render(
        LipSyncArtifact $lipSync,
        VideoRenderFormat $format,
        VideoRenderQuality $quality,
    ): FinalVideoArtifact {
        $inputPath = $lipSync->video()->storagePath();

        if ('' === trim($inputPath)) {
            throw new FFmpegProviderException('Lip-synced video storage path cannot be empty.');
        }

        $finalVideoId = FinalVideoId::generate();
        $extension = $format->value;
        $outputPath = rtrim($this->outputDirectory, '/\\')
            .DIRECTORY_SEPARATOR
            .$finalVideoId->value
            .'.'
            .$extension;

        $command = [
            $this->binary,
            '-y',
            '-i',
            $inputPath,
            '-t',
            (string) max(1.0, $lipSync->video()->duration()),
            ...$this->qualityFlags($quality),
            ...$this->formatFlags($format),
            $outputPath,
        ];

        $output = $this->processRunner->run($command);

        return $this->videoRenderMapper->toArtifact(
            $output,
            $lipSync,
            VideoRenderProvider::FFmpeg,
            $format,
            $quality,
            $finalVideoId,
        );
    }

    /**
     * @return list<string>
     */
    private function qualityFlags(VideoRenderQuality $quality): array
    {
        return match ($quality) {
            VideoRenderQuality::Preview => ['-crf', '28'],
            VideoRenderQuality::Standard => ['-crf', '23'],
            VideoRenderQuality::High => ['-crf', '18'],
        };
    }

    /**
     * @return list<string>
     */
    private function formatFlags(VideoRenderFormat $format): array
    {
        return match ($format) {
            VideoRenderFormat::MP4 => ['-c:v', 'libx264', '-c:a', 'aac'],
            VideoRenderFormat::WEBM => ['-c:v', 'libvpx-vp9', '-c:a', 'libopus'],
        };
    }
}
