<?php

declare(strict_types=1);

namespace App\Infrastructure\Speech;

use App\Domain\Speech\SpeechToTextProviderInterface;
use App\Domain\Speech\Transcript;
use App\Domain\Video\VideoJob;
use App\Infrastructure\Speech\Exception\FasterWhisperProviderException;

/**
 * Development-friendly STT provider that returns deterministic transcript segments
 * without invoking an external faster-whisper binary.
 */
final class DeterministicSpeechToTextProvider implements SpeechToTextProviderInterface
{
    public function __construct(
        private readonly FasterWhisperOutputParser $outputParser,
    ) {
    }

    public function transcribe(VideoJob $video): Transcript
    {
        $storagePath = $video->storagePath();

        if (null === $storagePath || '' === trim($storagePath)) {
            throw new FasterWhisperProviderException('Video job must have a storage path before transcription.');
        }

        return $this->transcribePath($storagePath);
    }

    public function transcribePath(string $storagePath): Transcript
    {
        $normalized = trim($storagePath);

        if ('' === $normalized) {
            throw new FasterWhisperProviderException('Storage path cannot be empty before transcription.');
        }

        $payload = json_encode([
            'language' => 'en',
            'segments' => [
                [
                    'index' => 0,
                    'start' => 0.0,
                    'end' => 2.5,
                    'text' => sprintf('Deterministic transcript for %s', basename($normalized)),
                ],
                [
                    'index' => 1,
                    'start' => 2.5,
                    'end' => 5.0,
                    'text' => 'Second deterministic segment.',
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        return $this->outputParser->parse($payload);
    }
}
