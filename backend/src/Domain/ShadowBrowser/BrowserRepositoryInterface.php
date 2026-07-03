<?php

declare(strict_types=1);

namespace App\Domain\ShadowBrowser;

interface BrowserRepositoryInterface
{
    public function findByScope(string $scopeKey): ?BrowserWorkspace;

    public function findById(BrowserWorkspaceId $id): ?BrowserWorkspace;

    public function save(BrowserWorkspace $workspace): void;
}
