<?php

declare(strict_types=1);

namespace App\Application\ShadowPresence;

use App\Domain\ShadowPresence\PresenceContext;
use App\Domain\ShadowPresence\PresenceSurface;
use App\Domain\ShadowPresence\PresenceWorkspace;
use App\Domain\ShadowPresence\ShadowPresenceRepositoryInterface;

final class PresenceCoordinator
{
    public function __construct(
        private readonly ShadowPresenceRepositoryInterface $repository,
        private readonly PresenceSessionManager $sessionManager,
        private readonly ContextHub $contextHub,
        private readonly PresenceAuditLog $auditLog,
        private readonly PresencePermissionEvaluator $permissionEvaluator,
    ) {
    }

    public function getWorkspace(string $scopeKey = 'default'): PresenceWorkspace
    {
        return $this->sessionManager->getWorkspace($scopeKey);
    }

    public function connect(
        string $scopeKey,
        PresenceSurface $surface,
        ?string $shadowSessionId = null,
    ): PresenceWorkspace {
        return $this->sessionManager->connect($scopeKey, $surface, $shadowSessionId);
    }

    public function disconnect(string $scopeKey = 'default'): PresenceWorkspace
    {
        return $this->sessionManager->disconnect($scopeKey);
    }

    /** @param array<string, mixed> $payload */
    public function updatePreferences(string $scopeKey, array $payload): PresenceWorkspace
    {
        $workspace = $this->getWorkspace($scopeKey);
        $preferences = $workspace->preferences();

        if (is_array($payload['permissions'] ?? null)) {
            $preferences = $this->permissionEvaluator->applyPermissionUpdates(
                $preferences,
                array_values(array_filter(
                    $payload['permissions'],
                    static fn ($item): bool => is_array($item),
                )),
            );
        }

        $surfaceEnabled = is_array($payload['surfaceEnabled'] ?? null) ? $payload['surfaceEnabled'] : null;

        $updated = $preferences->withUpdates(
            shortcuts: is_string($payload['shortcuts'] ?? null) ? $payload['shortcuts'] : null,
            notifications: is_bool($payload['notifications'] ?? null) ? $payload['notifications'] : null,
            voiceEnabled: is_bool($payload['voiceEnabled'] ?? null) ? $payload['voiceEnabled'] : null,
            proactiveEnabled: is_bool($payload['proactiveEnabled'] ?? null) ? $payload['proactiveEnabled'] : null,
            surfaceEnabled: is_array($surfaceEnabled)
                ? array_map(static fn ($value): bool => (bool) $value, $surfaceEnabled)
                : null,
        );

        $workspace = $workspace->updatePreferences($updated);
        $this->repository->save($workspace);

        return $workspace;
    }

    public function context(string $scopeKey, PresenceSurface $surface): PresenceContext
    {
        $workspace = $this->getWorkspace($scopeKey);
        $shadowSessionId = $workspace->activeSession()?->shadowSessionId();
        $context = $this->contextHub->build($scopeKey, $surface, $shadowSessionId);

        $event = $this->auditLog->recordContextAccess($workspace, $surface);
        $workspace = $workspace->recordEvent($event);
        $this->repository->save($workspace);

        return $context;
    }
}
