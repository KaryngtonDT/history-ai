<?php

declare(strict_types=1);

namespace App\Domain\Workspace;

use App\Domain\Video\VideoId;
use App\Domain\Workspace\Exception\InvalidProjectException;
use DateTimeImmutable;

final readonly class ProjectVideo
{
    public function __construct(
        private VideoId $videoId,
        private string $filename,
        private DateTimeImmutable $addedAt,
    ) {
        if ('' === trim($filename)) {
            throw new InvalidProjectException('Project video filename cannot be empty.');
        }
    }

    public static function create(VideoId $videoId, string $filename): self
    {
        return new self($videoId, trim($filename), new DateTimeImmutable());
    }

    public static function reconstitute(
        VideoId $videoId,
        string $filename,
        DateTimeImmutable $addedAt,
    ): self {
        return new self($videoId, trim($filename), $addedAt);
    }

    public function videoId(): VideoId
    {
        return $this->videoId;
    }

    public function filename(): string
    {
        return $this->filename;
    }

    public function addedAt(): DateTimeImmutable
    {
        return $this->addedAt;
    }
}
