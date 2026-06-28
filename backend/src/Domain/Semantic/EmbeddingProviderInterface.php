<?php

declare(strict_types=1);

namespace App\Domain\Semantic;

interface EmbeddingProviderInterface
{
    public function generateEmbedding(ChunkText $text): EmbeddingVector;
}
