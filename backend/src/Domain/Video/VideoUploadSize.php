<?php

declare(strict_types=1);

namespace App\Domain\Video;

use App\Domain\Video\Exception\InvalidVideoJobException;

final readonly class VideoUploadSize
{
    public static function assertWithinLimit(int $bytes, int $maxBytes): void
    {
        if ($bytes <= 0) {
            throw new InvalidVideoJobException('Video upload must contain at least one byte.');
        }

        if ($bytes > $maxBytes) {
            throw new InvalidVideoJobException(sprintf(
                'Video upload exceeds the maximum allowed size of %d bytes.',
                $maxBytes,
            ));
        }
    }
}
