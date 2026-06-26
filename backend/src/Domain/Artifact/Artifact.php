<?php

declare(strict_types=1);

namespace App\Domain\Artifact;

use App\Domain\Content\ContentId;
use App\Domain\Processing\ProcessingJobId;
use DateTimeImmutable;

final class Artifact
{
    private function __construct(
        private readonly ArtifactId $id,
        private readonly ContentId $contentId,
        private readonly ProcessingJobId $processingJobId,
        private readonly ArtifactType $type,
        private readonly ArtifactContent $content,
        private readonly DateTimeImmutable $createdAt,
    ) {
    }

    public static function create(
        ArtifactId $id,
        ContentId $contentId,
        ProcessingJobId $processingJobId,
        ArtifactType $type,
        ArtifactContent $content,
    ): self {
        return new self(
            $id,
            $contentId,
            $processingJobId,
            $type,
            $content,
            new DateTimeImmutable(),
        );
    }

    /**
     * Rebuilds an Artifact aggregate from persistence. Used by infrastructure only.
     */
    public static function reconstitute(
        ArtifactId $id,
        ContentId $contentId,
        ProcessingJobId $processingJobId,
        ArtifactType $type,
        ArtifactContent $content,
        DateTimeImmutable $createdAt,
    ): self {
        return new self(
            $id,
            $contentId,
            $processingJobId,
            $type,
            $content,
            $createdAt,
        );
    }

    public function id(): ArtifactId
    {
        return $this->id;
    }

    public function contentId(): ContentId
    {
        return $this->contentId;
    }

    public function processingJobId(): ProcessingJobId
    {
        return $this->processingJobId;
    }

    public function type(): ArtifactType
    {
        return $this->type;
    }

    public function content(): ArtifactContent
    {
        return $this->content;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
