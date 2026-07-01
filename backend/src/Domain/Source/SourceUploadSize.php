<?php

declare(strict_types=1);

namespace App\Domain\Source;

use App\Domain\Source\Exception\InvalidSourceException;

final readonly class SourceUploadSize
{
    public static function assertWithinLimit(int $bytes, int $maxBytes): void
    {
        if ($bytes <= 0) {
            throw new InvalidSourceException('Audio upload must contain at least one byte.');
        }

        if ($bytes > $maxBytes) {
            throw new InvalidSourceException(sprintf(
                'Audio upload exceeds the maximum allowed size of %d bytes.',
                $maxBytes,
            ));
        }
    }
}
