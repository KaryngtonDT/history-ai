<?php

declare(strict_types=1);

namespace App\Application\ShadowMemory\Handlers;

use App\Application\ShadowMemory\MemoryBuilder;
use App\Application\ShadowMemory\MemorySearchService;

final class PostMemorySearchHandler
{
    public function __construct(
        private readonly MemoryBuilder $builder,
        private readonly MemorySearchService $searchService,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function __invoke(string $scopeKey, array $payload): array
    {
        $query = is_string($payload['query'] ?? null) ? trim($payload['query']) : '';

        return $this->searchService->search($this->builder->ingestRelationship($scopeKey), $query);
    }
}
