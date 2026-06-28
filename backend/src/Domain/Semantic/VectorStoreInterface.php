<?php

declare(strict_types=1);

namespace App\Domain\Semantic;

interface VectorStoreInterface
{
    public function index(VectorDocumentCollection $documents): void;

    public function search(EmbeddingVector $query, int $limit = 5): VectorSearchResultCollection;
}
