<?php

declare(strict_types=1);

namespace App\Domain\ShadowSecondBrain;

interface KnowledgeWorkspaceRepositoryInterface
{
    public function findByScope(string $scopeKey): ?KnowledgeWorkspace;

    public function findById(KnowledgeWorkspaceId $id): ?KnowledgeWorkspace;

    public function save(KnowledgeWorkspace $workspace): void;
}
