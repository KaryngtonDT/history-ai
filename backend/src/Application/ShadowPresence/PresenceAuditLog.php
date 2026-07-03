<?php

declare(strict_types=1);

namespace App\Application\ShadowPresence;

use App\Domain\ShadowPresence\PresenceEvent;
use App\Domain\ShadowPresence\PresenceSurface;
use App\Domain\ShadowPresence\PresenceWorkspace;

final class PresenceAuditLog
{
    public function __construct(
        private readonly PresencePermissionEvaluator $permissionEvaluator,
    ) {
    }

    public function recordConnect(
        PresenceWorkspace $workspace,
        PresenceSurface $surface,
    ): PresenceEvent {
        $permissionsUsed = $this->permissionEvaluator->grantedCapabilities($workspace->preferences());

        return PresenceEvent::create(
            'Surface connected',
            $surface,
            'user_invoked',
            sprintf('Connected to Shadow from %s surface.', $surface->value),
            $permissionsUsed,
        );
    }

    public function recordDisconnect(
        PresenceWorkspace $workspace,
        PresenceSurface $surface,
    ): PresenceEvent {
        return PresenceEvent::create(
            'Surface disconnected',
            $surface,
            'user_invoked',
            sprintf('Disconnected from Shadow on %s surface.', $surface->value),
            [],
        );
    }

    public function recordContextAccess(
        PresenceWorkspace $workspace,
        PresenceSurface $surface,
    ): PresenceEvent {
        $permissionsUsed = $this->permissionEvaluator->grantedCapabilities($workspace->preferences());

        return PresenceEvent::create(
            'Context accessed',
            $surface,
            'context_hub',
            'Unified context assembled from existing Shadow engines.',
            $permissionsUsed,
        );
    }
}
