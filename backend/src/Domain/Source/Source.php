<?php

declare(strict_types=1);

namespace App\Domain\Source;

use App\Domain\Source\Exception\InvalidSourceException;
use DateTimeImmutable;

final readonly class Source
{
    public function __construct(
        private SourceId $id,
        private SourceType $type,
        private SourceMetadata $metadata,
        private SourceStatus $status,
        private DateTimeImmutable $createdAt,
        private ?string $storagePath = null,
    ) {
    }

    public static function createUploaded(
        SourceId $id,
        SourceType $type,
        SourceMetadata $metadata,
        ?DateTimeImmutable $createdAt = null,
    ): self {
        return new self(
            $id,
            $type,
            $metadata,
            SourceStatus::Uploaded,
            $createdAt ?? new DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        SourceId $id,
        SourceType $type,
        SourceMetadata $metadata,
        SourceStatus $status,
        DateTimeImmutable $createdAt,
        string $storagePath,
    ): self {
        return new self(
            $id,
            $type,
            $metadata,
            $status,
            $createdAt,
            $storagePath,
        );
    }

    public function withStoragePath(string $storagePath): self
    {
        $this->assertStatus(SourceStatus::Uploaded, 'store');

        $normalized = trim($storagePath);

        if ('' === $normalized) {
            throw new InvalidSourceException('Source storage path cannot be empty.');
        }

        return new self(
            $this->id,
            $this->type,
            $this->metadata,
            $this->status,
            $this->createdAt,
            $normalized,
        );
    }

    public function queue(): self
    {
        $this->assertStatus(SourceStatus::Uploaded, 'queue');

        if (null === $this->storagePath || '' === trim($this->storagePath)) {
            throw new InvalidSourceException('Source must be stored before it can be queued.');
        }

        return $this->withStatus(SourceStatus::Queued);
    }

    public function startProcessing(): self
    {
        $this->assertStatus(SourceStatus::Queued, 'start processing');

        return $this->withStatus(SourceStatus::Processing);
    }

    public function complete(): self
    {
        $this->assertStatus(SourceStatus::Processing, 'complete');

        return $this->withStatus(SourceStatus::Completed);
    }

    public function fail(): self
    {
        $this->assertStatus(SourceStatus::Processing, 'fail');

        return $this->withStatus(SourceStatus::Failed);
    }

    public function id(): SourceId
    {
        return $this->id;
    }

    public function type(): SourceType
    {
        return $this->type;
    }

    public function metadata(): SourceMetadata
    {
        return $this->metadata;
    }

    public function status(): SourceStatus
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

    private function withStatus(SourceStatus $status): self
    {
        return new self(
            $this->id,
            $this->type,
            $this->metadata,
            $status,
            $this->createdAt,
            $this->storagePath,
        );
    }

    private function assertStatus(SourceStatus $expected, string $action): void
    {
        if ($this->status !== $expected) {
            throw new InvalidSourceException(sprintf(
                'Cannot %s a source in status "%s".',
                $action,
                $this->status->value,
            ));
        }
    }
}
