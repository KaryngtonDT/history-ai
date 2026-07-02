<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowSecondBrain;

use App\Domain\ShadowSecondBrain\KnowledgeWorkspace;
use App\Domain\ShadowSecondBrain\KnowledgeWorkspaceId;
use App\Domain\ShadowSecondBrain\ShadowSecondBrainRepositoryInterface;

final class InMemoryShadowSecondBrainRepository implements ShadowSecondBrainRepositoryInterface
{
    /** @var array<string, KnowledgeWorkspace> */
    private array $workspaces = [];

    public function findByScope(string $scopeKey): ?KnowledgeWorkspace
    {
        foreach ($this->workspaces as $workspace) {
            if ($workspace->scopeKey() === $scopeKey) {
                return $workspace;
            }
        }

        return null;
    }

    public function findById(KnowledgeWorkspaceId $id): ?KnowledgeWorkspace
    {
        return $this->workspaces[$id->value] ?? null;
    }

    public function save(KnowledgeWorkspace $workspace): void
    {
        $this->workspaces[$workspace->id()->value] = $workspace;
    }
}
