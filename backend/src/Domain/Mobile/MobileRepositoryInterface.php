<?php

declare(strict_types=1);

namespace App\Domain\Mobile;

interface MobileRepositoryInterface
{
    public function findByScope(string $scopeKey): ?MobileWorkspace;

    public function findById(MobileWorkspaceId $id): ?MobileWorkspace;

    public function save(MobileWorkspace $workspace): void;
}
