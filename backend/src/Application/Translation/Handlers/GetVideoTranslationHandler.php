<?php

declare(strict_types=1);

namespace App\Application\Translation\Handlers;

use App\Application\Translation\DTO\VideoTranslationResult;
use App\Application\Translation\Queries\GetVideoTranslationQuery;
use App\Application\Translation\TranslationLanguageListParser;
use App\Domain\Translation\Exception\InvalidTranslationException;
use App\Domain\Translation\TranslationRepositoryInterface;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;

final class GetVideoTranslationHandler
{
    public function __construct(
        private readonly TranslationRepositoryInterface $translationRepository,
    ) {
    }

    public function __invoke(GetVideoTranslationQuery $query): VideoTranslationResult
    {
        try {
            $videoId = new VideoId($query->videoId);
        } catch (InvalidVideoIdException) {
            throw new InvalidTranslationException('Video id must be a valid UUID.');
        }

        $parsedLanguages = TranslationLanguageListParser::parse($query->language);

        if ([] === $parsedLanguages) {
            throw new InvalidTranslationException('Translation language must be a valid code.');
        }

        $translation = $this->translationRepository->findByVideoIdAndLanguage(
            $videoId,
            $parsedLanguages[0],
        );

        if (null === $translation) {
            throw new InvalidTranslationException(sprintf(
                'Translation for video "%s" in language "%s" was not found.',
                $query->videoId,
                $query->language,
            ));
        }

        return VideoTranslationResult::fromDomain($videoId->value, $translation);
    }
}
