<?php

declare(strict_types=1);

namespace App\Application\YouTube\Handlers;

use App\Application\YouTube\DTO\GetYouTubeResult;
use App\Application\YouTube\Queries\ListYouTubeQuery;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\YouTube\YouTubeVideoRepositoryInterface;

final class ListYouTubeHandler
{
    public function __construct(
        private readonly YouTubeVideoRepositoryInterface $youtubeVideoRepository,
        private readonly VideoRepositoryInterface $videoRepository,
    ) {
    }

    /**
     * @return list<GetYouTubeResult>
     */
    public function __invoke(ListYouTubeQuery $query): array
    {
        $results = [];

        foreach ($this->youtubeVideoRepository->findRecent($query->limit) as $video) {
            $job = $this->videoRepository->findById($video->videoId());

            if (null === $job) {
                continue;
            }

            $results[] = GetYouTubeResult::fromVideoAndStatus($video, $job->status());
        }

        return $results;
    }
}
