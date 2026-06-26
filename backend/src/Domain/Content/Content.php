<?php

declare(strict_types=1);

namespace App\Domain\Content;

use DateTimeImmutable;

final class Content
{
    private function __construct(
        private readonly ContentId $id,
        private ContentTitle $title,
        private readonly ContentSourceType $sourceType,
        private ContentStatus $status,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {
    }

    public static function create(
        ContentId $id,
        ContentTitle $title,
        ContentSourceType $sourceType,
    ): self {
        $now = new DateTimeImmutable();

        return new self(
            $id,
            $title,
            $sourceType,
            ContentStatus::Draft,
            $now,
            $now,
        );
    }

    /**
     * Rebuilds a Content aggregate from persistence. Used by infrastructure only.
     */
    public static function reconstitute(
        ContentId $id,
        ContentTitle $title,
        ContentSourceType $sourceType,
        ContentStatus $status,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self($id, $title, $sourceType, $status, $createdAt, $updatedAt);
    }

    public function id(): ContentId
    {
        return $this->id;
    }

    public function title(): ContentTitle
    {
        return $this->title;
    }

    public function sourceType(): ContentSourceType
    {
        return $this->sourceType;
    }

    public function status(): ContentStatus
    {
        return $this->status;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
