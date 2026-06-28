<?php

declare(strict_types=1);

namespace App\Application\Semantic\Handlers;

use App\Application\Semantic\DTO\SemanticSearchResult;
use App\Application\Semantic\Queries\SearchSemanticChunksQuery;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Content\ContentId;
use App\Domain\Semantic\ChunkCollection;
use App\Domain\Semantic\Chunker;
use App\Domain\Semantic\EmbeddingGeneratorInterface;
use App\Domain\Semantic\SemanticQuery;
use App\Domain\Semantic\SemanticRetriever;

final class SearchSemanticChunksHandler
{
    public function __construct(
        private readonly ArtifactRepositoryInterface $artifactRepository,
        private readonly Chunker $chunker,
        private readonly EmbeddingGeneratorInterface $embeddingGenerator,
        private readonly SemanticRetriever $semanticRetriever,
    ) {
    }

    public function __invoke(SearchSemanticChunksQuery $query): SemanticSearchResult
    {
        $artifacts = $this->artifactRepository->findByContentId(
            new ContentId($query->contentId),
        );

        if ([] === $artifacts) {
            return SemanticSearchResult::empty();
        }

        /** @var list<\App\Domain\Semantic\Chunk> $chunks */
        $chunks = [];

        foreach ($artifacts as $artifact) {
            foreach ($this->chunker->chunk($artifact)->chunks() as $chunk) {
                $chunks[] = $chunk;
            }
        }

        if ([] === $chunks) {
            return SemanticSearchResult::empty();
        }

        $embeddedChunks = $this->embeddingGenerator->generate(new ChunkCollection($chunks));

        if ($embeddedChunks->isEmpty()) {
            return SemanticSearchResult::empty();
        }

        $retrievedChunks = $this->semanticRetriever->retrieve(
            new SemanticQuery($query->query),
            $embeddedChunks,
            $this->embeddingGenerator,
        );

        return SemanticSearchResult::fromDomain($retrievedChunks);
    }
}
