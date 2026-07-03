<?php

declare(strict_types=1);

namespace App\Application\ShadowPresence;

use App\Domain\ShadowPresence\Exception\InvalidShadowPresenceException;
use App\Domain\ShadowPresence\PresenceSurface;
use App\Domain\ShadowPresence\PresenceWorkspace;
use App\Domain\ShadowPresence\ShadowPresenceRepositoryInterface;

final class PresenceSessionManager
{
    public function __construct(
        private readonly ShadowPresenceRepositoryInterface $repository,
        private readonly ConversationBridge $conversationBridge,
        private readonly PresenceConsentManager $consentManager,
        private readonly PresenceAuditLog $auditLog,
    ) {
    }

    public function getWorkspace(string $scopeKey = 'default'): PresenceWorkspace
    {
        return $this->repository->findByScope($scopeKey) ?? PresenceWorkspace::create(scopeKey: $scopeKey);
    }

    public function connect(
        string $scopeKey,
        PresenceSurface $surface,
        ?string $shadowSessionId = null,
    ): PresenceWorkspace {
        $workspace = $this->getWorkspace($scopeKey);

        if (null !== $shadowSessionId) {
            $this->conversationBridge->resolveShadowSessionId($shadowSessionId);
        }

        $preferences = $this->consentManager->applyConnectConsent($workspace->preferences());
        $workspace = $workspace->updatePreferences($preferences);
        $workspace = $workspace->connect($surface, $shadowSessionId);

        $event = $this->auditLog->recordConnect($workspace, $surface);
        $workspace = $workspace->recordEvent($event);

        $this->repository->save($workspace);

        return $workspace;
    }

    public function disconnect(string $scopeKey = 'default'): PresenceWorkspace
    {
        $workspace = $this->getWorkspace($scopeKey);
        $active = $workspace->activeSession();

        if (null === $active) {
            throw new InvalidShadowPresenceException('No active presence session to disconnect.');
        }

        $surface = $active->surface();
        $preferences = $this->consentManager->revokeTemporaryScopes($workspace->preferences());
        $workspace = $workspace->updatePreferences($preferences);
        $workspace = $workspace->disconnect();

        $event = $this->auditLog->recordDisconnect($workspace, $surface);
        $workspace = $workspace->recordEvent($event);

        $this->repository->save($workspace);

        return $workspace;
    }
}
