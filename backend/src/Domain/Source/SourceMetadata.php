<?php

declare(strict_types=1);

namespace App\Domain\Source;

use App\Domain\Source\Exception\InvalidSourceException;

final class SourceMetadata
{
    public function __construct(
        public string $originalFilename,
        public ?string $title = null,
        public ?string $language = null,
    ) {
        $this->originalFilename = trim($originalFilename);
        $this->assertValidFilename($this->originalFilename);
    }

    public function displayTitle(): string
    {
        if (null !== $this->title && '' !== trim($this->title)) {
            return trim($this->title);
        }

        $basename = pathinfo($this->originalFilename, PATHINFO_FILENAME);

        return '' !== $basename ? $basename : $this->originalFilename;
    }

    private function assertValidFilename(string $originalFilename): void
    {
        $trimmed = trim($originalFilename);

        if ('' === $trimmed) {
            throw new InvalidSourceException('Source original filename cannot be empty.');
        }

        if (str_contains($trimmed, "\0")) {
            throw new InvalidSourceException('Source original filename cannot contain null bytes.');
        }

        if (str_contains($trimmed, '/') || str_contains($trimmed, '\\')) {
            throw new InvalidSourceException('Source original filename cannot contain path separators.');
        }

        if (strlen($trimmed) > 255) {
            throw new InvalidSourceException('Source original filename cannot exceed 255 characters.');
        }
    }
}
