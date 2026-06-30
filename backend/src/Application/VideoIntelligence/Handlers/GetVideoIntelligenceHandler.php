<?php

declare(strict_types=1);

namespace App\Application\VideoIntelligence\Handlers;

use App\Application\VideoIntelligence\DTO\VideoIntelligenceResult;
use App\Application\VideoIntelligence\Queries\GetVideoIntelligenceQuery;
use App\Domain\Video\Exception\InvalidVideoJobException;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\VideoIntelligence\VideoIntelligenceFactoryInterface;

final class GetVideoIntelligenceHandler
{
    public function __construct(
        private readonly VideoRepositoryInterface $videoRepository,
        private readonly VideoIntelligenceFactoryInterface $videoIntelligenceFactory,
    ) {
    }

    public function __invoke(GetVideoIntelligenceQuery $query): VideoIntelligenceResult
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

        return VideoIntelligenceResult::fromIntelligence(
            $videoId->value,
            $this->videoIntelligenceFactory->fromVideoJob($job),
        );
    }
}
