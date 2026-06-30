<?php

declare(strict_types=1);

namespace App\Application\TTS\Handlers;

use App\Application\TTS\Commands\GenerateVideoAudioCommand;
use App\Application\TTS\VideoAudioGenerator;
use App\Application\Translation\TranslationLanguageListParser;
use App\Domain\TTS\Exception\InvalidAudioArtifactException;
use App\Domain\TTS\TextToSpeechProvider;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;

final class GenerateVideoAudioHandler
{
    public function __construct(
        private readonly VideoAudioGenerator $videoAudioGenerator,
    ) {
    }

    public function __invoke(GenerateVideoAudioCommand $command): void
    {
        try {
            $videoId = new VideoId($command->videoId);
        } catch (InvalidVideoIdException) {
            throw new InvalidAudioArtifactException('Video id must be a valid UUID.');
        }

        /** @var list<\App\Domain\Translation\TranslationLanguage> $targetLanguages */
        $targetLanguages = [];

        foreach ($command->targetLanguages as $languageCode) {
            $parsed = TranslationLanguageListParser::parse($languageCode);

            if ([] === $parsed) {
                throw new InvalidAudioArtifactException(sprintf('Invalid target language "%s".', $languageCode));
            }

            $targetLanguages[] = $parsed[0];
        }

        $provider = null;

        if (null !== $command->provider && '' !== trim($command->provider)) {
            $provider = TextToSpeechProvider::tryFrom(strtolower(trim($command->provider)));

            if (null === $provider) {
                throw new InvalidAudioArtifactException(sprintf('Invalid text-to-speech provider "%s".', $command->provider));
            }
        }

        $this->videoAudioGenerator->generate(
            $videoId,
            $provider,
            $command->voiceId,
            $targetLanguages,
        );
    }
}
