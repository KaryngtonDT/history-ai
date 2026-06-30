<?php

declare(strict_types=1);

namespace App\Application\LipSync\Handlers;

use App\Application\Translation\TranslationLanguageListParser;
use App\Application\LipSync\Commands\GenerateVideoLipSyncCommand;
use App\Application\LipSync\VideoLipSyncGenerator;
use App\Domain\LipSync\Exception\InvalidLipSyncException;
use App\Domain\LipSync\LipSyncProvider;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;

final class GenerateVideoLipSyncHandler
{
    public function __construct(
        private readonly VideoLipSyncGenerator $videoLipSyncGenerator,
    ) {
    }

    public function __invoke(GenerateVideoLipSyncCommand $command): void
    {
        try {
            $videoId = new VideoId($command->videoId);
        } catch (InvalidVideoIdException) {
            throw new InvalidLipSyncException('Video id must be a valid UUID.');
        }

        /** @var list<\App\Domain\Translation\TranslationLanguage> $targetLanguages */
        $targetLanguages = [];

        foreach ($command->targetLanguages as $languageCode) {
            $parsed = TranslationLanguageListParser::parse($languageCode);

            if ([] === $parsed) {
                throw new InvalidLipSyncException(sprintf('Invalid target language "%s".', $languageCode));
            }

            $targetLanguages[] = $parsed[0];
        }

        $provider = null;

        if (null !== $command->provider && '' !== trim($command->provider)) {
            $provider = LipSyncProvider::tryFrom(strtolower(trim($command->provider)));

            if (null === $provider) {
                throw new InvalidLipSyncException(sprintf('Invalid lip sync provider "%s".', $command->provider));
            }
        }

        $this->videoLipSyncGenerator->generate(
            $videoId,
            $provider,
            $targetLanguages,
        );
    }
}
