<?php

declare(strict_types=1);

namespace App\Application\Video\DTO;

use App\Domain\Video\VideoJob;

final readonly class GetVideoStatusResult
{
    public function __construct(
        public string $videoId,
        public string $status,
        public string $originalFilename,
        public string $language,
        public string $createdAt,
        public ?string $failureMessage = null,
        public ?string $failedStage = null,
        public ?float $lastProcessingDurationSeconds = null,
    ) {
    }

    public static function fromJob(VideoJob $job): self
    {
        return new self(
            $job->id()->value,
            $job->status()->value,
            $job->originalFilename(),
            $job->language()->value,
            $job->createdAt()->format(DATE_ATOM),
            $job->failureMessage(),
            $job->failedStage(),
            $job->lastProcessingDurationSeconds(),
        );
    }
}
