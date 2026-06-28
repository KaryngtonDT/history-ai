<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Semantic;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Semantic\Chunk;
use App\Domain\Semantic\ChunkId;
use App\Domain\Semantic\ChunkPosition;
use App\Domain\Semantic\ChunkText;
use App\Domain\Semantic\EmbeddingVector;
use App\Domain\Semantic\SimilarityScore;
use App\Domain\Semantic\VectorDocument;
use App\Domain\Semantic\VectorSearchResult;
use PHPUnit\Framework\TestCase;

final class VectorSearchResultTest extends TestCase
{
    public function testContainsVectorDocumentAndSimilarityScore(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $position = new ChunkPosition(0);
        $chunk = new Chunk(
            ChunkId::derive($artifactId, $position),
            $artifactId,
            ChunkText::fromString('## Roman Republic'),
            $position,
        );
        $document = new VectorDocument($chunk, new EmbeddingVector([0.1, 0.2]));
        $score = new SimilarityScore(0.87);

        $result = new VectorSearchResult($document, $score);

        self::assertSame($document, $result->document());
        self::assertSame($score, $result->score());
    }
}
