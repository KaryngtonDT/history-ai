<?php

declare(strict_types=1);

namespace App\Application\Speech\Handlers;

use App\Application\Speech\DTO\VideoTranscriptResult;
use App\Application\Speech\Queries\GetVideoTranscriptQuery;
use App\Domain\Speech\Exception\InvalidTranscriptException;
use App\Domain\Speech\TranscriptRepositoryInterface;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;

final class GetVideoTranscriptHandler
{
    public function __construct(
        private readonly TranscriptRepositoryInterface $transcriptRepository,
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
            throw new InvalidTranscriptException(sprintf(
                'Transcript for video "%s" was not found.',
                $query->videoId,
            ));
        }

        return VideoTranscriptResult::fromDomain($videoId->value, $transcript);
    }
}
