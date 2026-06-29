<?php

declare(strict_types=1);

namespace App\Infrastructure\Speech;

use App\Domain\Speech\SpeechToTextProviderInterface;
use App\Domain\Speech\Transcript;
use App\Domain\Video\VideoJob;
use App\Infrastructure\Speech\Exception\FasterWhisperProviderException;

final class FasterWhisperProvider implements SpeechToTextProviderInterface
{
    public function __construct(
        private readonly FasterWhisperProcessRunnerInterface $processRunner,
        private readonly FasterWhisperOutputParser $outputParser,
        private readonly string $binary,
        private readonly string $model,
    ) {
    }

    public function transcribe(VideoJob $video): Transcript
    {
        $storagePath = $video->storagePath();

        if (null === $storagePath || '' === trim($storagePath)) {
            throw new FasterWhisperProviderException('Video job must have a storage path before transcription.');
        }

        $command = [
            $this->binary,
            $storagePath,
            '--model',
            $this->model,
            '--output-format',
            'json',
        ];

        $output = $this->processRunner->run($command);

        return $this->outputParser->parse($output);
    }
}
