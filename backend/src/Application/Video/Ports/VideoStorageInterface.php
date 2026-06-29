<?php

declare(strict_types=1);

namespace App\Application\Video\Ports;

use App\Domain\Video\VideoId;

interface VideoStorageInterface
{
    public function store(VideoId $videoId, string $sourcePath, string $originalFilename): string;
}
