<?php

declare(strict_types=1);

namespace App\Domain\YouTube;

use App\Domain\Video\VideoId;

interface YouTubeVideoRepositoryInterface
{
    public function save(YouTubeVideo $video): void;

    public function findById(YouTubeVideoId $id): ?YouTubeVideo;

    public function findByVideoId(VideoId $videoId): ?YouTubeVideo;

    /**
     * @return list<YouTubeVideo>
     */
    public function findRecent(int $limit = 20): array;
}
