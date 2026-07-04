<?php

declare(strict_types=1);

namespace App\Application\ShadowBrowser;

use App\Application\ShadowPresence\ConversationBridge;
use App\Application\ShadowPresence\PresenceCoordinator;
use App\Domain\ShadowBrowser\BrowserContext;
use App\Domain\ShadowBrowser\BrowserPermission;
use App\Domain\ShadowBrowser\BrowserRepositoryInterface;
use App\Domain\ShadowBrowser\BrowserTab;
use App\Domain\ShadowBrowser\BrowserWorkspace;
use App\Domain\ShadowBrowser\Exception\InvalidShadowBrowserException;
use App\Domain\ShadowPresence\Exception\InvalidShadowPresenceException;
use App\Domain\ShadowPresence\PresenceSurface;

final class BrowserCoordinator
{
    public function __construct(
        private readonly BrowserSessionManager $sessionManager,
        private readonly PresenceCoordinator $presenceCoordinator,
        private readonly PlatformDetectionEngine $platformDetectionEngine,
        private readonly BrowserPermissionEvaluator $permissionEvaluator,
        private readonly BrowserAuditLog $auditLog,
        private readonly ConversationBridge $conversationBridge,
        private readonly BrowserRepositoryInterface $repository,
    ) {
    }

    public function getWorkspace(string $scopeKey = 'default'): BrowserWorkspace
    {
        return $this->sessionManager->getWorkspace($scopeKey);
    }

    public function connect(string $scopeKey, ?string $shadowSessionId = null): BrowserWorkspace
    {
        $this->presenceCoordinator->updatePreferences($scopeKey, [
            'surfaceEnabled' => [PresenceSurface::Browser->value => true],
        ]);
        $this->presenceCoordinator->connect($scopeKey, PresenceSurface::Browser, $shadowSessionId);

        return $this->sessionManager->connect($scopeKey, $shadowSessionId);
    }

    public function disconnect(string $scopeKey = 'default'): BrowserWorkspace
    {
        $workspace = $this->sessionManager->disconnect($scopeKey);

        try {
            $this->presenceCoordinator->disconnect($scopeKey);
        } catch (InvalidShadowPresenceException) {
            // Browser and presence sessions can be disconnected independently.
        }

        return $workspace;
    }

    /** @param array<string, mixed> $payload */
    public function updateContext(string $scopeKey, array $payload): BrowserWorkspace
    {
        $workspace = $this->getWorkspace($scopeKey);
        $active = $workspace->activeSession();

        if (null === $active) {
            throw new InvalidShadowBrowserException('No active browser session for context update.');
        }

        $url = is_string($payload['url'] ?? null) ? trim($payload['url']) : '';
        $tabId = is_string($payload['tabId'] ?? null) ? trim($payload['tabId']) : '';

        if ('' === $url || '' === $tabId) {
            throw new InvalidShadowBrowserException('Browser context requires url and tabId.');
        }

        $title = is_string($payload['title'] ?? null) ? trim($payload['title']) : '';
        $selection = is_string($payload['selection'] ?? null) ? trim($payload['selection']) : null;
        $platform = $this->platformDetectionEngine->detect($url);

        $host = $this->platformDetectionEngine->extractHost($url);
        $this->permissionEvaluator->isGranted($workspace, $host, BrowserPermission::ReadPageContext);

        $tab = BrowserTab::create($tabId, $url, $title, $platform, $selection);
        $workspace = $workspace->updateActiveTab($tab);

        $shadowSessionId = $active->shadowSessionId();
        $conversationSessionId = $this->conversationBridge->resolveConversationSessionId($shadowSessionId);
        $context = BrowserContext::fromTab($scopeKey, $tab, $shadowSessionId, $conversationSessionId);
        $workspace = $workspace->updateContext($context);

        $activity = $this->auditLog->recordContextUpdate($workspace, $url, $platform);
        $workspace = $workspace->recordActivity($activity);

        $this->repository->save($workspace);

        return $workspace;
    }

    public function detectPlatform(string $scopeKey, string $url): array
    {
        $workspace = $this->getWorkspace($scopeKey);
        $platform = $this->platformDetectionEngine->detect($url);

        $activity = $this->auditLog->recordPlatformDetection($workspace, $url, $platform);
        $workspace = $workspace->recordActivity($activity);
        $this->repository->save($workspace);

        return [
            'platform' => $platform,
            'host' => $this->platformDetectionEngine->extractHost($url),
        ];
    }

    /** @param list<array<string, mixed>> $sitePolicies */
    public function updatePermissions(string $scopeKey, array $sitePolicies): BrowserWorkspace
    {
        $workspace = $this->getWorkspace($scopeKey);
        $workspace = $this->permissionEvaluator->applySitePolicyUpdates($workspace, $sitePolicies);
        $this->repository->save($workspace);

        return $workspace;
    }

    public function saveWorkspace(BrowserWorkspace $workspace): void
    {
        $this->repository->save($workspace);
    }
}
