<?php

declare(strict_types=1);

namespace App\Domain\Workspace;

use App\Domain\Video\VideoId;
use App\Domain\Workspace\Exception\InvalidProjectException;

final readonly class ProjectVideoCollection
{
    /** @var list<ProjectVideo> */
    private array $videos;

    /**
     * @param list<ProjectVideo> $videos
     */
    public function __construct(array $videos = [])
    {
        $seen = [];

        foreach ($videos as $video) {
            $key = $video->videoId()->value;

            if (isset($seen[$key])) {
                throw new InvalidProjectException(sprintf(
                    'Duplicate project video "%s".',
                    $key,
                ));
            }

            $seen[$key] = true;
        }

        $this->videos = array_values($videos);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<ProjectVideo>
     */
    public function all(): array
    {
        return $this->videos;
    }

    public function count(): int
    {
        return count($this->videos);
    }

    public function isEmpty(): bool
    {
        return [] === $this->videos;
    }

    public function contains(VideoId $videoId): bool
    {
        foreach ($this->videos as $video) {
            if ($video->videoId()->equals($videoId)) {
                return true;
            }
        }

        return false;
    }

    public function add(ProjectVideo $video): self
    {
        if ($this->contains($video->videoId())) {
            throw new InvalidProjectException(sprintf(
                'Video "%s" is already in the project.',
                $video->videoId()->value,
            ));
        }

        return new self([...$this->videos, $video]);
    }

    public function remove(VideoId $videoId): self
    {
        $remaining = array_values(array_filter(
            $this->videos,
            static fn (ProjectVideo $video): bool => !$video->videoId()->equals($videoId),
        ));

        if (count($remaining) === count($this->videos)) {
            throw new InvalidProjectException(sprintf(
                'Video "%s" is not in the project.',
                $videoId->value,
            ));
        }

        return new self($remaining);
    }

    /**
     * @return list<VideoId>
     */
    public function videoIds(): array
    {
        return array_map(
            static fn (ProjectVideo $video): VideoId => $video->videoId(),
            $this->videos,
        );
    }
}
