<?php

declare(strict_types=1);

namespace App\Application\VideoRender\Handlers;

use App\Application\Translation\TranslationLanguageListParser;
use App\Application\VideoRender\DTO\VideoRenderResult;
use App\Application\VideoRender\Queries\GetVideoRenderQuery;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;
use App\Domain\VideoRender\Exception\InvalidVideoRenderException;
use App\Domain\VideoRender\FinalVideoRepositoryInterface;

final class GetVideoRenderHandler
{
    public function __construct(
        private readonly FinalVideoRepositoryInterface $finalVideoRepository,
    ) {
    }

    public function __invoke(GetVideoRenderQuery $query): VideoRenderResult
    {
        try {
            $videoId = new VideoId($query->videoId);
        } catch (InvalidVideoIdException) {
            throw new InvalidVideoRenderException('Video id must be a valid UUID.');
        }

        $parsed = TranslationLanguageListParser::parse($query->language);

        if ([] === $parsed) {
            throw new InvalidVideoRenderException(sprintf('Invalid target language "%s".', $query->language));
        }

        $artifact = $this->finalVideoRepository->findByVideoIdAndLanguage($videoId, $parsed[0]);

        if (null === $artifact) {
            throw new InvalidVideoRenderException('Final render not found for the requested language.');
        }

        return new VideoRenderResult(
            videoId: $videoId->value,
            finalVideoId: $artifact->finalVideoId()->value,
            targetLanguage: $parsed[0]->value,
            provider: $artifact->provider()->value,
            format: $artifact->format()->value,
            quality: $artifact->quality()->value,
            duration: $artifact->duration(),
            fileSizeBytes: $artifact->fileSizeBytes(),
        );
    }
}
