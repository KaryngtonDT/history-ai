<?php

declare(strict_types=1);

namespace App\Application\LipSync\Handlers;

use App\Application\LipSync\DTO\VideoLipSyncSummary;
use App\Application\LipSync\Queries\ListVideoLipSyncQuery;
use App\Domain\LipSync\Exception\InvalidLipSyncException;
use App\Domain\LipSync\LipSyncRepositoryInterface;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;

final class ListVideoLipSyncHandler
{
    public function __construct(
        private readonly LipSyncRepositoryInterface $lipSyncRepository,
    ) {
    }

    /**
     * @return list<VideoLipSyncSummary>
     */
    public function __invoke(ListVideoLipSyncQuery $query): array
    {
        try {
            $videoId = new VideoId($query->videoId);
        } catch (InvalidVideoIdException) {
            throw new InvalidLipSyncException('Video id must be a valid UUID.');
        }

        $entries = $this->lipSyncRepository->findAllDetailedByVideoId($videoId);
        $summaries = [];

        foreach ($entries as $entry) {
            $artifact = $entry['artifact'];

            $summaries[] = new VideoLipSyncSummary(
                videoId: $videoId->value,
                artifactId: $artifact->artifactId()->value,
                clonedAudioId: $artifact->audio()->value,
                targetLanguage: $entry['language']->value,
                provider: $artifact->provider()->value,
                synchronizedVideoId: $artifact->video()->synchronizedVideoId()->value,
                duration: $artifact->video()->duration(),
            );
        }

        return $summaries;
    }
}
