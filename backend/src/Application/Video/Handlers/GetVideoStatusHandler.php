<?php

declare(strict_types=1);

namespace App\Application\Video\Handlers;

use App\Application\Video\DTO\GetVideoStatusResult;
use App\Application\Video\Queries\GetVideoStatusQuery;
use App\Domain\Video\Exception\InvalidVideoJobException;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoRepositoryInterface;

final class GetVideoStatusHandler
{
    public function __construct(
        private readonly VideoRepositoryInterface $videoRepository,
    ) {
    }

    public function __invoke(GetVideoStatusQuery $query): GetVideoStatusResult
    {
        try {
            $videoId = new VideoId($query->videoId);
        } catch (InvalidVideoJobException) {
            throw new InvalidVideoJobException('Video not found.');
        }

        $job = $this->videoRepository->findById($videoId);

        if (null === $job) {
            throw new InvalidVideoJobException('Video not found.');
        }

        return GetVideoStatusResult::fromJob($job);
    }
}
