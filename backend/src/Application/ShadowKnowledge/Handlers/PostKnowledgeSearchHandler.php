<?php

declare(strict_types=1);

namespace App\Application\ShadowKnowledge\Handlers;

use App\Application\ShadowKnowledge\KnowledgeBuilder;
use App\Application\ShadowKnowledge\KnowledgeSearchService;

final class PostKnowledgeSearchHandler
{
    public function __construct(
        private readonly KnowledgeBuilder $builder,
        private readonly KnowledgeSearchService $searchService,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function __invoke(string $scopeKey, array $payload): array
    {
        $query = is_string($payload['query'] ?? null) ? trim($payload['query']) : '';

        return $this->searchService->search($this->builder->syncGraph($scopeKey), $query);
    }
}
