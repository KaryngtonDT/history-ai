<?php

declare(strict_types=1);

namespace App\Application\VoiceClone\Handlers;

use App\Application\Translation\TranslationLanguageListParser;
use App\Application\VoiceClone\Commands\GenerateVideoVoiceCloneCommand;
use App\Application\VoiceClone\VideoVoiceCloneGenerator;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;
use App\Domain\VoiceClone\Exception\InvalidVoiceCloneException;
use App\Domain\VoiceClone\VoiceCloneProvider;

final class GenerateVideoVoiceCloneHandler
{
    public function __construct(
        private readonly VideoVoiceCloneGenerator $videoVoiceCloneGenerator,
    ) {
    }

    public function __invoke(GenerateVideoVoiceCloneCommand $command): void
    {
        try {
            $videoId = new VideoId($command->videoId);
        } catch (InvalidVideoIdException) {
            throw new InvalidVoiceCloneException('Video id must be a valid UUID.');
        }

        /** @var list<\App\Domain\Translation\TranslationLanguage> $targetLanguages */
        $targetLanguages = [];

        foreach ($command->targetLanguages as $languageCode) {
            $parsed = TranslationLanguageListParser::parse($languageCode);

            if ([] === $parsed) {
                throw new InvalidVoiceCloneException(sprintf('Invalid target language "%s".', $languageCode));
            }

            $targetLanguages[] = $parsed[0];
        }

        $provider = null;

        if (null !== $command->provider && '' !== trim($command->provider)) {
            $provider = VoiceCloneProvider::tryFrom(strtolower(trim($command->provider)));

            if (null === $provider) {
                throw new InvalidVoiceCloneException(sprintf('Invalid voice clone provider "%s".', $command->provider));
            }
        }

        $this->videoVoiceCloneGenerator->generate(
            $videoId,
            $provider,
            $targetLanguages,
        );
    }
}
