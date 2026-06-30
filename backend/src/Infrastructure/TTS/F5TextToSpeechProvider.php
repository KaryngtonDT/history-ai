<?php

declare(strict_types=1);

namespace App\Infrastructure\TTS;

use App\Domain\Translation\Translation;
use App\Domain\TTS\AudioArtifact;
use App\Domain\TTS\AudioId;
use App\Domain\TTS\TextToSpeechProvider;
use App\Domain\TTS\TextToSpeechProviderInterface;
use App\Domain\TTS\Voice;
use App\Domain\TTS\VoiceCatalog;
use App\Domain\TTS\VoiceCollection;
use App\Infrastructure\TTS\Exception\F5TextToSpeechProviderException;

final class F5TextToSpeechProvider implements TextToSpeechProviderInterface
{
    public function __construct(
        private readonly F5ProcessRunnerInterface $processRunner,
        private readonly AudioMapper $audioMapper,
        private readonly string $binary,
        private readonly string $model,
        private readonly string $basePath,
        private readonly string $outputDirectory,
    ) {
    }

    public function synthesize(Translation $translation, Voice $voice): AudioArtifact
    {
        $text = trim($translation->text());

        if ('' === $text) {
            throw new F5TextToSpeechProviderException('Translation text cannot be empty for synthesis.');
        }

        $audioId = AudioId::generate();
        $outputPath = rtrim($this->outputDirectory, '/\\').DIRECTORY_SEPARATOR.$audioId->value.'.wav';

        $command = [
            $this->binary,
            '--text',
            $text,
            '--voice',
            $voice->voiceId(),
            '--model',
            $this->model,
            '--base-path',
            $this->basePath,
            '--output',
            $outputPath,
        ];

        $output = $this->processRunner->run($command);

        return $this->audioMapper->toArtifact(
            $output,
            $translation->translationId(),
            TextToSpeechProvider::F5TTS,
            $voice,
            $audioId,
            $outputPath,
            $translation->targetLanguage(),
        );
    }

    public function availableVoices(): VoiceCollection
    {
        return VoiceCatalog::defaultVoices();
    }
}
