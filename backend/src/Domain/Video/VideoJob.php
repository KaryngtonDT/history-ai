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
        private ?string $storagePath = null,
        private ?string $failureMessage = null,
        private ?string $failedStage = null,
        private ?float $lastProcessingDurationSeconds = null,
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

    public static function reconstitute(
        VideoId $id,
        string $originalFilename,
        VideoLanguage $language,
        VideoStatus $status,
        DateTimeImmutable $createdAt,
        string $storagePath,
        ?string $failureMessage = null,
        ?string $failedStage = null,
        ?float $lastProcessingDurationSeconds = null,
    ): self {
        return new self(
            $id,
            $originalFilename,
            $language,
            $status,
            $createdAt,
            $storagePath,
            $failureMessage,
            $failedStage,
            $lastProcessingDurationSeconds,
        );
    }

    public function withStoragePath(string $storagePath): self
    {
        $this->assertStatus(VideoStatus::Uploaded, 'store');

        $normalized = trim($storagePath);

        if ('' === $normalized) {
            throw new InvalidVideoJobException('Video storage path cannot be empty.');
        }

        return new self(
            $this->id,
            $this->originalFilename,
            $this->language,
            $this->status,
            $this->createdAt,
            $normalized,
        );
    }

    public function queue(): self
    {
        $this->assertStatus(VideoStatus::Uploaded, 'queue');

        if (null === $this->storagePath || '' === trim($this->storagePath)) {
            throw new InvalidVideoJobException('Video job must be stored before it can be queued.');
        }

        return $this->withStatus(VideoStatus::Queued);
    }

    public function startProcessing(): self
    {
        $allowed = [VideoStatus::Queued, VideoStatus::Completed, VideoStatus::Failed];

        if (!in_array($this->status, $allowed, true)) {
            throw new InvalidVideoJobException(sprintf(
                'Cannot start processing a video job in status "%s".',
                $this->status->value,
            ));
        }

        return $this->withStatus(VideoStatus::Processing)->withoutFailureDetails();
    }

    public function complete(): self
    {
        $this->assertStatus(VideoStatus::Processing, 'complete');

        return $this->withStatus(VideoStatus::Completed);
    }

    public function fail(
        ?string $failureMessage = null,
        ?string $failedStage = null,
        ?float $processingDurationSeconds = null,
    ): self {
        $this->assertStatus(VideoStatus::Processing, 'fail');

        $normalizedMessage = null !== $failureMessage ? trim($failureMessage) : null;

        if ('' === $normalizedMessage) {
            $normalizedMessage = null;
        }

        $normalizedStage = null !== $failedStage ? trim($failedStage) : null;

        if ('' === $normalizedStage) {
            $normalizedStage = null;
        }

        return $this->withStatus(VideoStatus::Failed)
            ->withFailureDetails(
                $normalizedMessage,
                $normalizedStage,
                null !== $processingDurationSeconds ? max(0.0, $processingDurationSeconds) : null,
            );
    }

    public function requeue(): self
    {
        if (VideoStatus::Failed === $this->status) {
            return $this->withStatus(VideoStatus::Queued);
        }

        if (VideoStatus::Queued === $this->status || VideoStatus::Processing === $this->status) {
            return $this;
        }

        throw new InvalidVideoJobException(sprintf(
            'Cannot requeue a video job in status "%s".',
            $this->status->value,
        ));
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

    public function storagePath(): ?string
    {
        return $this->storagePath;
    }

    public function failureMessage(): ?string
    {
        return $this->failureMessage;
    }

    public function failedStage(): ?string
    {
        return $this->failedStage;
    }

    public function lastProcessingDurationSeconds(): ?float
    {
        return $this->lastProcessingDurationSeconds;
    }

    private function withStatus(VideoStatus $status): self
    {
        return new self(
            $this->id,
            $this->originalFilename,
            $this->language,
            $status,
            $this->createdAt,
            $this->storagePath,
            $this->failureMessage,
            $this->failedStage,
            $this->lastProcessingDurationSeconds,
        );
    }

    private function withFailureDetails(
        ?string $failureMessage,
        ?string $failedStage,
        ?float $lastProcessingDurationSeconds,
    ): self {
        return new self(
            $this->id,
            $this->originalFilename,
            $this->language,
            $this->status,
            $this->createdAt,
            $this->storagePath,
            $failureMessage,
            $failedStage,
            $lastProcessingDurationSeconds,
        );
    }

    private function withoutFailureDetails(): self
    {
        return new self(
            $this->id,
            $this->originalFilename,
            $this->language,
            $this->status,
            $this->createdAt,
            $this->storagePath,
            null,
            null,
            null,
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
