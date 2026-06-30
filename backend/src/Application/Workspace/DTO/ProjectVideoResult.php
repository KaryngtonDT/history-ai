<?php

declare(strict_types=1);

namespace App\Application\Workspace\DTO;

use App\Domain\Workspace\BatchJob;
use App\Domain\Workspace\Project;
use App\Domain\Workspace\ProjectVideo;

final readonly class ProjectVideoResult
{
    public function __construct(
        public string $videoId,
        public string $filename,
        public string $addedAt,
    ) {
    }

    public static function fromVideo(ProjectVideo $video): self
    {
        return new self(
            $video->videoId()->value,
            $video->filename(),
            $video->addedAt()->format(DATE_ATOM),
        );
    }
}
