<?php

declare(strict_types=1);

namespace App\Application\Translation\Handlers;

use App\Application\Translation\Commands\GenerateVideoTranslationsCommand;
use App\Application\Translation\TranslationLanguageListParser;
use App\Application\Translation\VideoTranslationGenerator;
use App\Domain\Translation\Exception\InvalidTranslationException;
use App\Domain\Translation\TranslationProvider;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;

final class GenerateVideoTranslationsHandler
{
    public function __construct(
        private readonly VideoTranslationGenerator $videoTranslationGenerator,
    ) {
    }

    public function __invoke(GenerateVideoTranslationsCommand $command): void
    {
        try {
            $videoId = new VideoId($command->videoId);
        } catch (InvalidVideoIdException) {
            throw new InvalidTranslationException('Video id must be a valid UUID.');
        }

        if ([] === $command->targetLanguages) {
            throw new InvalidTranslationException('At least one target language is required.');
        }

        /** @var list<\App\Domain\Translation\TranslationLanguage> $targetLanguages */
        $targetLanguages = [];

        foreach ($command->targetLanguages as $languageCode) {
            $parsed = TranslationLanguageListParser::parse($languageCode);

            if ([] === $parsed) {
                throw new InvalidTranslationException(sprintf('Invalid target language "%s".', $languageCode));
            }

            $targetLanguages[] = $parsed[0];
        }

        $provider = null;

        if (null !== $command->provider && '' !== trim($command->provider)) {
            $provider = TranslationProvider::tryFrom(strtolower(trim($command->provider)));

            if (null === $provider) {
                throw new InvalidTranslationException(sprintf('Invalid translation provider "%s".', $command->provider));
            }
        }

        $this->videoTranslationGenerator->generate($videoId, $targetLanguages, $provider);
    }
}
