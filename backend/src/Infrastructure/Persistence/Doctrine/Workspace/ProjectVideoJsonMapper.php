<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Workspace;

use App\Domain\Video\VideoId;
use App\Domain\Workspace\Project;
use App\Domain\Workspace\ProjectId;
use App\Domain\Workspace\ProjectVideo;
use App\Domain\Workspace\ProjectVideoCollection;
use DateTimeImmutable;
use JsonException;

final class ProjectVideoJsonMapper
{
    /**
     * @return list<array{videoId: string, filename: string, addedAt: string}>
     */
    public function toArray(ProjectVideoCollection $videos): array
    {
        $items = [];

        foreach ($videos->all() as $video) {
            $items[] = [
                'videoId' => $video->videoId()->value,
                'filename' => $video->filename(),
                'addedAt' => $video->addedAt()->format(DATE_ATOM),
            ];
        }

        return $items;
    }

    public function toJson(ProjectVideoCollection $videos): string
    {
        return json_encode($this->toArray($videos), JSON_THROW_ON_ERROR);
    }

    public function fromJson(string $json): ProjectVideoCollection
    {
        try {
            /** @var mixed $decoded */
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return ProjectVideoCollection::empty();
        }

        if (!is_array($decoded)) {
            return ProjectVideoCollection::empty();
        }

        $videos = [];

        foreach ($decoded as $item) {
            if (!is_array($item)) {
                continue;
            }

            $videoId = is_string($item['videoId'] ?? null) ? $item['videoId'] : null;
            $filename = is_string($item['filename'] ?? null) ? $item['filename'] : null;
            $addedAt = is_string($item['addedAt'] ?? null) ? $item['addedAt'] : null;

            if (null === $videoId || null === $filename) {
                continue;
            }

            $videos[] = ProjectVideo::reconstitute(
                new VideoId($videoId),
                $filename,
                null !== $addedAt ? new DateTimeImmutable($addedAt) : new DateTimeImmutable(),
            );
        }

        return new ProjectVideoCollection($videos);
    }
}
