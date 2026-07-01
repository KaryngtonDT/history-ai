<?php

declare(strict_types=1);

namespace App\Domain\YouTube;

use App\Domain\Video\VideoId;
use App\Domain\Video\VideoStatus;

final readonly class YouTubeImportResult
{
    public function __construct(
        public YouTubeVideoId $youtubeId,
        public VideoId $videoId,
        public YouTubeMetadata $metadata,
        public VideoStatus $status,
        public string $url,
    ) {
    }
}
