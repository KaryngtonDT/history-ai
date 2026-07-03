<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowPresence;

use App\Domain\ShadowPresence\PresenceWorkspace;
use App\Domain\ShadowPresence\PresenceWorkspaceId;
use App\Domain\ShadowPresence\ShadowPresenceRepositoryInterface;

final class InMemoryShadowPresenceRepository implements ShadowPresenceRepositoryInterface
{
    /** @var array<string, PresenceWorkspace> */
    private array $workspaces = [];

    public function findByScope(string $scopeKey): ?PresenceWorkspace
    {
        foreach ($this->workspaces as $workspace) {
            if ($workspace->scopeKey() === $scopeKey) {
                return $workspace;
            }
        }

        return null;
    }

    public function findById(PresenceWorkspaceId $id): ?PresenceWorkspace
    {
        return $this->workspaces[$id->value] ?? null;
    }

    public function save(PresenceWorkspace $workspace): void
    {
        $this->workspaces[$workspace->id()->value] = $workspace;
    }
}
