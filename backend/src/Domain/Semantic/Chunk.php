<?php

declare(strict_types=1);

namespace App\Domain\Semantic;

use App\Domain\Artifact\ArtifactId;

final readonly class Chunk
{
    public function __construct(
        private ChunkId $id,
        private ArtifactId $artifactId,
        private ChunkText $text,
        private ChunkPosition $position,
    ) {
    }

    public function id(): ChunkId
    {
        return $this->id;
    }

    public function artifactId(): ArtifactId
    {
        return $this->artifactId;
    }

    public function text(): ChunkText
    {
        return $this->text;
    }

    public function position(): ChunkPosition
    {
        return $this->position;
    }
}
