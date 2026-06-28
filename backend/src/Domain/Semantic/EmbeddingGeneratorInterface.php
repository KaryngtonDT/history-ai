<?php

declare(strict_types=1);

namespace App\Domain\Semantic;

interface EmbeddingGeneratorInterface
{
    public function generate(ChunkCollection $chunks): EmbeddedChunkCollection;
}
