<?php

declare(strict_types=1);

namespace App\Domain\ShadowPresence;

interface ShadowPresenceRepositoryInterface
{
    public function findByScope(string $scopeKey): ?PresenceWorkspace;

    public function findById(PresenceWorkspaceId $id): ?PresenceWorkspace;

    public function save(PresenceWorkspace $workspace): void;
}
