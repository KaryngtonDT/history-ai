<?php

declare(strict_types=1);

namespace App\Domain\Artifact;

interface ArtifactRepositoryInterface
{
    public function save(Artifact $artifact): void;

    public function findById(ArtifactId $id): ?Artifact;
}
