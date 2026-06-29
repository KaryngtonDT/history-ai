<?php

declare(strict_types=1);

namespace App\Application\Video\DTO;

use App\Domain\Video\VideoId;
use App\Domain\Video\VideoStatus;

final readonly class UploadVideoResult
{
    public function __construct(
        public VideoId $videoId,
        public VideoStatus $status,
    ) {
    }
}
