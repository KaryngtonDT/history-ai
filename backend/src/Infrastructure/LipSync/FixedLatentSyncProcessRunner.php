<?php

declare(strict_types=1);

namespace App\Infrastructure\LipSync;

final class FixedLatentSyncProcessRunner implements LatentSyncProcessRunner
{
    /**
     * @param list<string> $command
     */
    public function run(array $command): string
    {
        $outputPath = $this->extractOutputPath($command);
        $audioDuration = $this->extractAudioDuration($command);

        if (null !== $outputPath) {
            $directory = dirname($outputPath);

            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            file_put_contents($outputPath, $this->minimalMp4Header());
        }

        return json_encode([
            'duration' => max(1.0, $audioDuration),
            'output' => $outputPath,
        ], JSON_THROW_ON_ERROR);
    }

    /**
     * @param list<string> $command
     */
    private function extractOutputPath(array $command): ?string
    {
        foreach ($command as $index => $part) {
            if ('--output' === $part && isset($command[$index + 1])) {
                return $command[$index + 1];
            }
        }

        return null;
    }

    /**
     * @param list<string> $command
     */
    private function extractAudioDuration(array $command): float
    {
        foreach ($command as $index => $part) {
            if ('--audio-duration' === $part && isset($command[$index + 1])) {
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
