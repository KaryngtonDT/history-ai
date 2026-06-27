<?php

declare(strict_types=1);

namespace App\Domain\Library;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Content\ContentId;
use DateTimeImmutable;

final class LibraryItem
{
    private function __construct(
        private readonly LibraryItemId $id,
        private readonly ContentId $contentId,
        private readonly ArtifactId $artifactId,
        private readonly LibraryItemType $type,
        private readonly LibraryItemTitle $title,
        private readonly DateTimeImmutable $createdAt,
    ) {
    }

    public static function create(
        LibraryItemId $id,
        ContentId $contentId,
        ArtifactId $artifactId,
        LibraryItemType $type,
        LibraryItemTitle $title,
    ): self {
        return new self(
            $id,
            $contentId,
            $artifactId,
            $type,
            $title,
            new DateTimeImmutable(),
        );
    }

    /**
     * Rebuilds a LibraryItem aggregate from persistence. Used by infrastructure only.
     */
    public static function reconstitute(
        LibraryItemId $id,
        ContentId $contentId,
        ArtifactId $artifactId,
        LibraryItemType $type,
        LibraryItemTitle $title,
        DateTimeImmutable $createdAt,
    ): self {
        return new self(
            $id,
            $contentId,
            $artifactId,
            $type,
            $title,
            $createdAt,
        );
    }

    public function id(): LibraryItemId
    {
        return $this->id;
    }

    public function contentId(): ContentId
    {
        return $this->contentId;
    }

    public function artifactId(): ArtifactId
    {
        return $this->artifactId;
    }

    public function type(): LibraryItemType
    {
        return $this->type;
    }

    public function title(): LibraryItemTitle
    {
        return $this->title;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
