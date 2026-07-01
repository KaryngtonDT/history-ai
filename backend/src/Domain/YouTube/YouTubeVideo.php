<?php

declare(strict_types=1);

namespace App\Domain\YouTube;

use App\Domain\Video\VideoId;
use DateTimeImmutable;

final readonly class YouTubeVideo
{
    public function __construct(
        private YouTubeVideoId $id,
        private string $url,
        private YouTubeMetadata $metadata,
        private VideoId $videoId,
        private DateTimeImmutable $importedAt,
    ) {
    }

    public static function create(
        YouTubeVideoId $id,
        string $url,
        YouTubeMetadata $metadata,
        VideoId $videoId,
        ?DateTimeImmutable $importedAt = null,
    ): self {
        YouTubeUrl::assertValid($url);

        return new self(
            $id,
            $url,
            $metadata,
            $videoId,
            $importedAt ?? new DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        YouTubeVideoId $id,
        string $url,
        YouTubeMetadata $metadata,
        VideoId $videoId,
        DateTimeImmutable $importedAt,
    ): self {
        return new self($id, $url, $metadata, $videoId, $importedAt);
    }

    public function id(): YouTubeVideoId
    {
        return $this->id;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function metadata(): YouTubeMetadata
    {
        return $this->metadata;
    }

    public function videoId(): VideoId
    {
        return $this->videoId;
    }

    public function importedAt(): DateTimeImmutable
    {
        return $this->importedAt;
    }
}
