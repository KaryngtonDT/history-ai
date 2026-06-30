<?php

declare(strict_types=1);

namespace App\Application\VideoRender\Handlers;

use App\Application\Translation\TranslationLanguageListParser;
use App\Application\VideoRender\Commands\GenerateVideoRenderCommand;
use App\Application\VideoRender\VideoFinalRenderGenerator;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;
use App\Domain\VideoRender\Exception\InvalidVideoRenderException;
use App\Domain\VideoRender\VideoRenderFormat;
use App\Domain\VideoRender\VideoRenderProvider;
use App\Domain\VideoRender\VideoRenderQuality;

final class GenerateVideoRenderHandler
{
    public function __construct(
        private readonly VideoFinalRenderGenerator $videoFinalRenderGenerator,
    ) {
    }

    public function __invoke(GenerateVideoRenderCommand $command): void
    {
        try {
            $videoId = new VideoId($command->videoId);
        } catch (InvalidVideoIdException) {
            throw new InvalidVideoRenderException('Video id must be a valid UUID.');
        }

        /** @var list<\App\Domain\Translation\TranslationLanguage> $targetLanguages */
        $targetLanguages = [];

        foreach ($command->targetLanguages as $languageCode) {
            $parsed = TranslationLanguageListParser::parse($languageCode);

            if ([] === $parsed) {
                throw new InvalidVideoRenderException(sprintf('Invalid target language "%s".', $languageCode));
            }

            $targetLanguages[] = $parsed[0];
        }

        $provider = null;

        if (null !== $command->provider && '' !== trim($command->provider)) {
            $provider = VideoRenderProvider::tryFrom(strtolower(trim($command->provider)));

            if (null === $provider) {
                throw new InvalidVideoRenderException(sprintf('Invalid render provider "%s".', $command->provider));
            }
        }

        $format = null;

        if (null !== $command->format && '' !== trim($command->format)) {
            $format = VideoRenderFormat::tryFrom(strtolower(trim($command->format)));

            if (null === $format) {
                throw new InvalidVideoRenderException(sprintf('Invalid render format "%s".', $command->format));
            }
        }

        $quality = null;

        if (null !== $command->quality && '' !== trim($command->quality)) {
            $quality = VideoRenderQuality::tryFrom(strtolower(trim($command->quality)));

            if (null === $quality) {
                throw new InvalidVideoRenderException(sprintf('Invalid render quality "%s".', $command->quality));
            }
        }

        $this->videoFinalRenderGenerator->generate(
            $videoId,
            $provider,
            $format,
            $quality,
            $targetLanguages,
        );
    }
}
