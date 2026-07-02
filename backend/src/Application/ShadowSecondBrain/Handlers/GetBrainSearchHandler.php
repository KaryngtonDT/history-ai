<?php

declare(strict_types=1);

namespace App\Application\ShadowSecondBrain\Handlers;

use App\Application\ShadowSecondBrain\BrainJsonMapper;
use App\Application\ShadowSecondBrain\KnowledgeWorkspaceSearch;
use App\Application\ShadowSecondBrain\WorkspaceBuilder;

final class GetBrainSearchHandler
{
    public function __construct(
        private readonly WorkspaceBuilder $builder,
        private readonly KnowledgeWorkspaceSearch $search,
        private readonly BrainJsonMapper $mapper,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey, string $query): array
    {
        $workspace = $this->builder->getWorkspace($scopeKey);
        $results = $this->search->search($workspace->entries(), $query);

        return $this->mapper->searchResults($workspace, $results, $query);
    }
}
