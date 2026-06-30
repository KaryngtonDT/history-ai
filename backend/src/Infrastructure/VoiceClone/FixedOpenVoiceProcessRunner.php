<?php

declare(strict_types=1);

namespace App\Infrastructure\VoiceClone;

final class FixedOpenVoiceProcessRunner implements OpenVoiceProcessRunnerInterface
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

            file_put_contents($outputPath, $this->minimalWavHeader());
        }

        return json_encode([
            'duration' => max(1.0, $sourceDuration),
            'sampleRate' => 44100,
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
    private function extractSourceDuration(array $command): float
    {
        foreach ($command as $index => $part) {
            if ('--source-duration' === $part && isset($command[$index + 1])) {
                return (float) $command[$index + 1];
            }
        }

        return 3.0;
    }

    private function minimalWavHeader(): string
    {
        return "RIFF\x24\x08\x00\x00WAVEfmt \x10\x00\x00\x00\x01\x00\x01\x00\x44\xAC\x00\x00\x88\x58\x01\x00\x02\x00\x10\x00data\x00\x08\x00\x00";
    }
}
