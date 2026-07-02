<?php

declare(strict_types=1);

namespace App\Application\ShadowSecondBrain\Handlers;

use App\Application\ShadowSecondBrain\BrainJsonMapper;
use App\Application\ShadowSecondBrain\KnowledgeDiffEngine;
use App\Application\ShadowSecondBrain\WorkspaceBuilder;

final class GetBrainDiffHandler
{
    public function __construct(
        private readonly WorkspaceBuilder $builder,
        private readonly KnowledgeDiffEngine $diffEngine,
        private readonly BrainJsonMapper $mapper,
    ) {
    }

    /**
     * @param list<string> $conceptKeys
     *
     * @return array<string, mixed>
     */
    public function __invoke(
        string $scopeKey,
        string $resourceType,
        string $resourceId,
        array $conceptKeys,
    ): array {
        $workspace = $this->builder->getWorkspace($scopeKey);
        $diff = $this->diffEngine->diff($resourceType, $resourceId, $conceptKeys, $workspace);

        return $this->mapper->diff($diff);
    }
}
