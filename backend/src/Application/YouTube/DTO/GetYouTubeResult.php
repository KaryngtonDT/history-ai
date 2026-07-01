<?php

declare(strict_types=1);

namespace App\Application\YouTube\DTO;

use App\Domain\Video\VideoStatus;
use App\Domain\YouTube\YouTubeMetadata;
use App\Domain\YouTube\YouTubeVideo;
use App\Domain\YouTube\YouTubeVideoId;

final readonly class GetYouTubeResult
{
    public function __construct(
        public YouTubeVideoId $youtubeId,
        public string $videoId,
        public string $url,
        public YouTubeMetadata $metadata,
        public VideoStatus $videoStatus,
        public string $importedAt,
    ) {
    }

    public static function fromVideoAndStatus(YouTubeVideo $video, VideoStatus $videoStatus): self
    {
        return new self(
            youtubeId: $video->id(),
            videoId: $video->videoId()->value,
            url: $video->url(),
            metadata: $video->metadata(),
            videoStatus: $videoStatus,
            importedAt: $video->importedAt()->format(DATE_ATOM),
        );
    }
}
