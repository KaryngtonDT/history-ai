<?php

declare(strict_types=1);

namespace App\Application\Video\Ports;

use App\Domain\Video\VideoId;

interface VideoProcessingQueueInterface
{
    public function enqueue(VideoId $videoId): void;
}
