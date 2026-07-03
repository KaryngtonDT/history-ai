<?php

declare(strict_types=1);

namespace App\Application\ShadowBrowser;

use App\Application\ShadowPresence\ConversationBridge;
use App\Domain\ShadowBrowser\Exception\InvalidShadowBrowserException;
use App\Domain\ShadowBrowser\BrowserRepositoryInterface;
use App\Domain\ShadowBrowser\BrowserWorkspace;

final class BrowserSessionManager
{
    public function __construct(
        private readonly BrowserRepositoryInterface $repository,
        private readonly ConversationBridge $conversationBridge,
        private readonly BrowserAuditLog $auditLog,
    ) {
    }

    public function getWorkspace(string $scopeKey = 'default'): BrowserWorkspace
    {
        return $this->repository->findByScope($scopeKey) ?? BrowserWorkspace::create(scopeKey: $scopeKey);
    }

    public function connect(string $scopeKey, ?string $shadowSessionId = null): BrowserWorkspace
    {
        $workspace = $this->getWorkspace($scopeKey);

        if (null !== $shadowSessionId) {
            $this->conversationBridge->resolveShadowSessionId($shadowSessionId);
        }

        $workspace = $workspace->connect($shadowSessionId);

        $activity = $this->auditLog->recordConnect($workspace);
        $workspace = $workspace->recordActivity($activity);

        $this->repository->save($workspace);

        return $workspace;
    }

    public function disconnect(string $scopeKey = 'default'): BrowserWorkspace
    {
        $workspace = $this->getWorkspace($scopeKey);

        if (null === $workspace->activeSession()) {
            throw new InvalidShadowBrowserException('No active browser session to disconnect.');
        }

        $workspace = $workspace->disconnect();

        $activity = $this->auditLog->recordDisconnect($workspace);
        $workspace = $workspace->recordActivity($activity);

        $this->repository->save($workspace);

        return $workspace;
    }
}
