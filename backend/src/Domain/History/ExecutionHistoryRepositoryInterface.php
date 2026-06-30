<?php

declare(strict_types=1);

namespace App\Domain\History;

use App\Domain\Video\VideoId;

interface ExecutionHistoryRepositoryInterface
{
    public function save(ExecutionHistory $history): void;

    public function findByVideoId(VideoId $videoId): ?ExecutionHistory;

    public function findOrCreateForVideo(VideoId $videoId): ExecutionHistory;
}