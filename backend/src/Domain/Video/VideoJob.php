<?php

declare(strict_types=1);

namespace App\Domain\Video;

use App\Domain\Video\Exception\InvalidVideoJobException;
use DateTimeImmutable;

final readonly class VideoJob
{
    private string $originalFilename;

    public function __construct(
        private VideoId $id,
        string $originalFilename,
        private VideoLanguage $language,
        private VideoStatus $status,
        private DateTimeImmutable $createdAt,
    ) {
        $this->originalFilename = self::normalizeFilename($originalFilename);
    }

    public static function createUploaded(
        VideoId $id,
        string $originalFilename,
        VideoLanguage $language,
        ?DateTimeImmutable $createdAt = null,
    ): self {
        return new self(
            $id,
            $originalFilename,
            $language,
            VideoStatus::Uploaded,
            $createdAt ?? new DateTimeImmutable(),
        );
    }

    public function queue(): self
    {
        $this->assertStatus(VideoStatus::Uploaded, 'queue');

        return $this->withStatus(VideoStatus::Queued);
    }

    public function startProcessing(): self
    {
        $this->assertStatus(VideoStatus::Queued, 'start processing');

        return $this->withStatus(VideoStatus::Processing);
    }

    public function complete(): self
    {
        $this->assertStatus(VideoStatus::Processing, 'complete');

        return $this->withStatus(VideoStatus::Completed);
    }

    public function fail(): self
    {
        $this->assertStatus(VideoStatus::Processing, 'fail');

        return $this->withStatus(VideoStatus::Failed);
    }

    public function id(): VideoId
    {
        return $this->id;
    }

    public function originalFilename(): string
    {
        return $this->originalFilename;
    }

    public function language(): VideoLanguage
    {
        return $this->language;
    }

    public function status(): VideoStatus
    {
        return $this->status;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    private function withStatus(VideoStatus $status): self
    {
        return new self(
            $this->id,
            $this->originalFilename,
            $this->language,
            $status,
            $this->createdAt,
        );
    }

    private function assertStatus(VideoStatus $expected, string $action): void
    {
        if ($this->status !== $expected) {
            throw new InvalidVideoJobException(sprintf(
                'Cannot %s a video job in status "%s".',
                $action,
                $this->status->value,
            ));
        }
    }

    private static function normalizeFilename(string $originalFilename): string
    {
        $trimmed = trim($originalFilename);

        if ('' === $trimmed) {
            throw new InvalidVideoJobException('Video original filename cannot be empty.');
        }

        if (str_contains($trimmed, "\0")) {
            throw new InvalidVideoJobException('Video original filename cannot contain null bytes.');
        }

        if (str_contains($trimmed, '/') || str_contains($trimmed, '\\')) {
            throw new InvalidVideoJobException('Video original filename cannot contain path separators.');
        }

        if (strlen($trimmed) > 255) {
            throw new InvalidVideoJobException('Video original filename cannot exceed 255 characters.');
        }

        return $trimmed;
    }
}
