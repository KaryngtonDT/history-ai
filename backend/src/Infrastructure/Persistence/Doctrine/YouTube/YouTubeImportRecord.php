<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\YouTube;

use App\Domain\Video\VideoId;
use App\Domain\YouTube\YouTubeMetadata;
use App\Domain\YouTube\YouTubeVideo;
use App\Domain\YouTube\YouTubeVideoId;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'youtube_import')]
class YouTubeImportRecord
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID)]
    private string $id;

    #[ORM\Column(name: 'video_id', type: Types::GUID)]
    private string $videoId;

    #[ORM\Column(name: 'youtube_url', length: 512)]
    private string $youtubeUrl;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(name: 'duration_seconds', nullable: true)]
    private ?int $durationSeconds = null;

    #[ORM\Column(name: 'thumbnail_url', length: 512, nullable: true)]
    private ?string $thumbnailUrl = null;

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $language = null;

    #[ORM\Column(name: 'channel_name', length: 255, nullable: true)]
    private ?string $channelName = null;

    #[ORM\Column(name: 'imported_at', type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $importedAt;

    private function __construct()
    {
    }

    public static function fromDomain(YouTubeVideo $video): self
    {
        $record = new self();
        $record->syncFromDomain($video);
        $record->importedAt = $video->importedAt();

        return $record;
    }

    public function syncFromDomain(YouTubeVideo $video): void
    {
        $metadata = $video->metadata();
        $this->id = $video->id()->value;
        $this->videoId = $video->videoId()->value;
        $this->youtubeUrl = $video->url();
        $this->title = $metadata->title;
        $this->durationSeconds = $metadata->durationSeconds;
        $this->thumbnailUrl = $metadata->thumbnailUrl;
        $this->language = $metadata->language;
        $this->channelName = $metadata->channelName;
    }

    public function toDomain(): YouTubeVideo
    {
        return YouTubeVideo::reconstitute(
            new YouTubeVideoId($this->id),
            $this->youtubeUrl,
            new YouTubeMetadata(
                $this->title,
                $this->durationSeconds,
                $this->thumbnailUrl,
                $this->language,
                $this->channelName,
            ),
            new VideoId($this->videoId),
            $this->importedAt,
        );
    }
}
