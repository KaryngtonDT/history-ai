<?php

declare(strict_types=1);

namespace App\Application\Translation\Handlers;

use App\Application\Translation\DTO\VideoTranslationSummary;
use App\Application\Translation\Queries\ListVideoTranslationsQuery;
use App\Domain\Translation\Exception\InvalidTranslationException;
use App\Domain\Translation\TranslationRepositoryInterface;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;

final class ListVideoTranslationsHandler
{
    public function __construct(
        private readonly TranslationRepositoryInterface $translationRepository,
    ) {
    }

    /**
     * @return list<VideoTranslationSummary>
     */
    public function __invoke(ListVideoTranslationsQuery $query): array
    {
        try {
            $videoId = new VideoId($query->videoId);
        } catch (InvalidVideoIdException) {
            throw new InvalidTranslationException('Video id must be a valid UUID.');
        }

        $translations = $this->translationRepository->findAllByVideoId($videoId);
        $summaries = [];

        foreach ($translations as $translation) {
            $summaries[] = new VideoTranslationSummary(
                videoId: $videoId->value,
                translationId: $translation->translationId()->value,
                sourceLanguage: $translation->sourceLanguage()->value,
                targetLanguage: $translation->targetLanguage()->value,
                provider: $translation->provider()->value,
                text: $translation->text(),
                segmentCount: $translation->segmentCount(),
            );
        }

        return $summaries;
    }
}
