<?php

declare(strict_types=1);

namespace App\Application\VideoRender\Handlers;

use App\Application\VideoRender\DTO\VideoRenderSummary;
use App\Application\VideoRender\Queries\ListVideoRenderQuery;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;
use App\Domain\VideoRender\Exception\InvalidVideoRenderException;
use App\Domain\VideoRender\FinalVideoRepositoryInterface;

final class ListVideoRenderHandler
{
    public function __construct(
        private readonly FinalVideoRepositoryInterface $finalVideoRepository,
    ) {
    }

    /**
     * @return list<VideoRenderSummary>
     */
    public function __invoke(ListVideoRenderQuery $query): array
    {
        try {
            $videoId = new VideoId($query->videoId);
        } catch (InvalidVideoIdException) {
            throw new InvalidVideoRenderException('Video id must be a valid UUID.');
        }

        $entries = $this->finalVideoRepository->findAllDetailedByVideoId($videoId);
        $summaries = [];

        foreach ($entries as $entry) {
            $artifact = $entry['artifact'];

            $summaries[] = new VideoRenderSummary(
                videoId: $videoId->value,
                finalVideoId: $artifact->finalVideoId()->value,
                targetLanguage: $entry['language']->value,
                provider: $artifact->provider()->value,
                format: $artifact->format()->value,
                quality: $artifact->quality()->value,
                duration: $artifact->duration(),
                fileSizeBytes: $artifact->fileSizeBytes(),
            );
        }

        return $summaries;
    }
}
