<?php

declare(strict_types=1);

namespace App\Application\YouTube\Handlers;

use App\Application\YouTube\DTO\GetYouTubeResult;
use App\Application\YouTube\Queries\GetYouTubeQuery;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\YouTube\Exception\InvalidYouTubeException;
use App\Domain\YouTube\YouTubeVideoId;
use App\Domain\YouTube\YouTubeVideoRepositoryInterface;

final class GetYouTubeHandler
{
    public function __construct(
        private readonly YouTubeVideoRepositoryInterface $youtubeVideoRepository,
        private readonly VideoRepositoryInterface $videoRepository,
    ) {
    }

    public function __invoke(GetYouTubeQuery $query): GetYouTubeResult
    {
        $video = $this->youtubeVideoRepository->findById(new YouTubeVideoId($query->youtubeId));

        if (null === $video) {
            throw new InvalidYouTubeException('YouTube import not found.');
        }

        $job = $this->videoRepository->findById($video->videoId());

        if (null === $job) {
            throw new InvalidYouTubeException('Linked video job not found.');
        }

        return GetYouTubeResult::fromVideoAndStatus($video, $job->status());
    }
}
