<?php

declare(strict_types=1);

namespace App\Application\Speech\Handlers;

use App\Application\Speech\DTO\VideoTranscriptResult;
use App\Application\Speech\Queries\GetVideoTranscriptQuery;
use App\Domain\Speech\Exception\InvalidTranscriptException;
use App\Domain\Speech\Exception\TranscriptNotFoundException;
use App\Domain\Speech\TranscriptRepositoryInterface;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoRepositoryInterface;

final class GetVideoTranscriptHandler
{
    public function __construct(
        private readonly TranscriptRepositoryInterface $transcriptRepository,
        private readonly VideoRepositoryInterface $videoRepository,
    ) {
    }

    public function __invoke(GetVideoTranscriptQuery $query): VideoTranscriptResult
    {
        try {
            $videoId = new VideoId($query->videoId);
        } catch (InvalidVideoIdException) {
            throw new InvalidTranscriptException('Video id must be a valid UUID.');
        }

        $transcript = $this->transcriptRepository->findByVideoId($videoId);

        if (null === $transcript) {
            $job = $this->videoRepository->findById($videoId);

            throw new TranscriptNotFoundException(
                sprintf('Transcript for video "%s" was not found.', $query->videoId),
                null !== $job ? $job->status()->value : null,
                $job?->failureMessage(),
                $job?->failedStage(),
                $job?->lastProcessingDurationSeconds(),
            );
        }

        return VideoTranscriptResult::fromDomain($videoId->value, $transcript);
    }
}
