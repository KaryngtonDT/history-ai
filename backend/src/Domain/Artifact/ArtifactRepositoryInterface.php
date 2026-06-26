<?php

declare(strict_types=1);

namespace App\Domain\Artifact;

use App\Domain\Content\ContentId;

interface ArtifactRepositoryInterface
{
    public function save(Artifact $artifact): void;

    public function findById(ArtifactId $id): ?Artifact;

    /**
     * @return list<Artifact>
     */
    public function findByContentId(ContentId $contentId): array;
}
