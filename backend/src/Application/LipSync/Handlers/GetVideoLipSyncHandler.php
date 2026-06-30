<?php

declare(strict_types=1);

namespace App\Application\LipSync\Handlers;

use App\Application\LipSync\DTO\VideoLipSyncResult;
use App\Application\LipSync\Queries\GetVideoLipSyncQuery;
use App\Application\Translation\TranslationLanguageListParser;
use App\Domain\LipSync\Exception\InvalidLipSyncException;
use App\Domain\LipSync\LipSyncRepositoryInterface;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoRepositoryInterface;

final class GetVideoLipSyncHandler
{
    public function __construct(
        private readonly LipSyncRepositoryInterface $lipSyncRepository,
        private readonly VideoRepositoryInterface $videoRepository,
    ) {
    }

    public function __invoke(GetVideoLipSyncQuery $query): VideoLipSyncResult
    {
        try {
            $videoId = new VideoId($query->videoId);
        } catch (InvalidVideoIdException) {
            throw new InvalidLipSyncException('Video id must be a valid UUID.');
        }

        $parsed = TranslationLanguageListParser::parse($query->language);

        if ([] === $parsed) {
            throw new InvalidLipSyncException(sprintf('Invalid target language "%s".', $query->language));
        }

        $artifact = $this->lipSyncRepository->findByVideoIdAndLanguage($videoId, $parsed[0]);

        if (null === $artifact) {
            throw new InvalidLipSyncException('Lip sync not found for the requested language.');
        }

        $video = $this->videoRepository->findById($videoId);
        $originalVideoPath = $video?->storagePath() ?? '';

        return new VideoLipSyncResult(
            videoId: $videoId->value,
            artifactId: $artifact->artifactId()->value,
            clonedAudioId: $artifact->audio()->value,
            targetLanguage: $parsed[0]->value,
            provider: $artifact->provider()->value,
            synchronizedVideoId: $artifact->video()->synchronizedVideoId()->value,
            duration: $artifact->video()->duration(),
            originalVideoPath: $originalVideoPath,
        );
    }
}
