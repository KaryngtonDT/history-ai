<?php

declare(strict_types=1);

namespace App\Infrastructure\Semantic;

use App\Domain\Semantic\ChunkText;
use App\Domain\Semantic\EmbeddingProviderInterface;
use App\Domain\Semantic\EmbeddingVector;

final class DeterministicEmbeddingProvider implements EmbeddingProviderInterface
{
    private const int VECTOR_DIMENSION = 8;

    public function generateEmbedding(ChunkText $text): EmbeddingVector
    {
        $hash = hash('sha256', $text->value());
        /** @var list<float> $values */
        $values = [];

        for ($index = 0; $index < self::VECTOR_DIMENSION; ++$index) {
            $hex = substr($hash, $index * 2, 2);
            $values[] = ((float) hexdec($hex) / 255.0) * 2.0 - 1.0;
        }

        return new EmbeddingVector($values);
    }
}
