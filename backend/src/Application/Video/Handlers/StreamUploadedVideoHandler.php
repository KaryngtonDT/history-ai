<?php

declare(strict_types=1);

namespace App\Application\Video\Handlers;

use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\Exception\InvalidVideoJobException;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoRepositoryInterface;

final class StreamUploadedVideoHandler
{
    public function __construct(
        private readonly VideoRepositoryInterface $videoRepository,
    ) {
    }

    public function __invoke(string $videoId): string
    {
        try {
            $id = new VideoId($videoId);
        } catch (InvalidVideoIdException) {
            throw new InvalidVideoJobException('Video id must be a valid UUID.');
        }

        $job = $this->videoRepository->findById($id);

        if (null === $job) {
            throw new InvalidVideoJobException('Video not found.');
        }

        $path = $job->storagePath();

        if (null === $path || '' === trim($path)) {
            throw new InvalidVideoJobException('Uploaded video file is not available.');
        }

        if (!is_file($path)) {
            throw new InvalidVideoJobException('Uploaded video file is not available on disk.');
        }

        return $path;
    }
}
