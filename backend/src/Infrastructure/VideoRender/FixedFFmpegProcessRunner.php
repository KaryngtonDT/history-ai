<?php

declare(strict_types=1);

namespace App\Infrastructure\VideoRender;

final class FixedFFmpegProcessRunner implements FFmpegProcessRunner
{
    /**
     * @param list<string> $command
     */
    public function run(array $command): string
    {
        $outputPath = $this->extractOutputPath($command);
        $sourceDuration = $this->extractSourceDuration($command);

        if (null !== $outputPath) {
            $directory = dirname($outputPath);

            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            $content = $this->minimalMp4Header();
            file_put_contents($outputPath, $content);
        }

        $fileSize = null !== $outputPath && is_file($outputPath) ? filesize($outputPath) : 1024;

        return json_encode([
            'duration' => max(1.0, $sourceDuration),
            'fileSizeBytes' => false !== $fileSize ? $fileSize : 1024,
            'output' => $outputPath,
        ], JSON_THROW_ON_ERROR);
    }

    /**
     * @param list<string> $command
     */
    private function extractOutputPath(array $command): ?string
    {
        $last = end($command);

        return is_string($last) && str_contains($last, '.mp4') ? $last : null;
    }

    /**
     * @param list<string> $command
     */
    private function extractSourceDuration(array $command): float
    {
        foreach ($command as $index => $part) {
            if ('-t' === $part && isset($command[$index + 1])) {
                return (float) $command[$index + 1];
            }
        }

        return 3.0;
    }

    private function minimalMp4Header(): string
    {
        return "\x00\x00\x00\x18ftypmp42\x00\x00\x00\x00mp42isom";
    }
}
