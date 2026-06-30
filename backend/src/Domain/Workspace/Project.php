<?php

declare(strict_types=1);

namespace App\Domain\Workspace;

use App\Domain\Video\VideoId;
use App\Domain\Workspace\Exception\InvalidProjectException;
use DateTimeImmutable;

final readonly class Project
{
    public function __construct(
        private ProjectId $id,
        private string $name,
        private DateTimeImmutable $createdAt,
        private ProjectVideoCollection $videos,
    ) {
        if ('' === trim($name)) {
            throw new InvalidProjectException('Project name cannot be empty.');
        }
    }

    public static function create(ProjectId $id, string $name): self
    {
        return new self(
            $id,
            trim($name),
            new DateTimeImmutable(),
            ProjectVideoCollection::empty(),
        );
    }

    public static function reconstitute(
        ProjectId $id,
        string $name,
        DateTimeImmutable $createdAt,
        ProjectVideoCollection $videos,
    ): self {
        return new self($id, trim($name), $createdAt, $videos);
    }

    public function rename(string $name): self
    {
        return new self(
            $this->id,
            trim($name),
            $this->createdAt,
            $this->videos,
        );
    }

    public function addVideo(ProjectVideo $video): self
    {
        return new self(
            $this->id,
            $this->name,
            $this->createdAt,
            $this->videos->add($video),
        );
    }

    public function removeVideo(VideoId $videoId): self
    {
        return new self(
            $this->id,
            $this->name,
            $this->createdAt,
            $this->videos->remove($videoId),
        );
    }

    public function id(): ProjectId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function videos(): ProjectVideoCollection
    {
        return $this->videos;
    }
}
