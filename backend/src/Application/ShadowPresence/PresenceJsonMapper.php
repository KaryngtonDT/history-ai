<?php

declare(strict_types=1);

namespace App\Application\ShadowPresence;

use App\Domain\ShadowPresence\PresenceCapability;
use App\Domain\ShadowPresence\PresenceContext;
use App\Domain\ShadowPresence\PresenceEvent;
use App\Domain\ShadowPresence\PresencePreferences;
use App\Domain\ShadowPresence\PresenceSession;
use App\Domain\ShadowPresence\PresenceSurface;
use App\Domain\ShadowPresence\PresenceWorkspace;

final class PresenceJsonMapper
{
    /** @return array<string, mixed> */
    public function session(?PresenceSession $session): array
    {
        if (null === $session) {
            return [
                'active' => false,
                'session' => null,
            ];
        }

        return [
            'active' => true,
            'session' => [
                'id' => $session->id(),
                'scopeKey' => $session->scopeKey(),
                'surface' => $session->surface()->value,
                'state' => $session->state()->value,
                'shadowSessionId' => $session->shadowSessionId(),
                'connectedAt' => $session->connectedAt()->format(\DateTimeInterface::ATOM),
                'lastActiveAt' => $session->lastActiveAt()->format(\DateTimeInterface::ATOM),
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function context(PresenceContext $context): array
    {
        return [
            'scopeKey' => $context->scopeKey(),
            'surface' => $context->surface()->value,
            'identityLabel' => $context->identityLabel(),
            'conceptCount' => $context->conceptCount(),
            'activeMissionTitle' => $context->activeMissionTitle(),
            'executiveHint' => $context->executiveHint(),
            'conversationSessionId' => $context->conversationSessionId(),
            'explainability' => $context->explainability(),
        ];
    }

    /** @return array<string, mixed> */
    public function preferences(PresencePreferences $preferences): array
    {
        return [
            'shortcuts' => $preferences->shortcuts(),
            'notifications' => $preferences->notifications(),
            'voiceEnabled' => $preferences->voiceEnabled(),
            'proactiveEnabled' => $preferences->proactiveEnabled(),
            'surfaceEnabled' => $preferences->surfaceEnabled(),
            'permissions' => array_map(
                static fn ($permission): array => [
                    'capability' => $permission->capability()->value,
                    'granted' => $permission->granted(),
                ],
                $preferences->permissions()->all(),
            ),
        ];
    }

    /** @return array<string, mixed> */
    public function history(PresenceWorkspace $workspace): array
    {
        return [
            'scopeKey' => $workspace->scopeKey(),
            'events' => array_map(
                fn (PresenceEvent $event): array => $this->eventToArray($event),
                $workspace->events()->all(),
            ),
        ];
    }

    /** @return array<string, mixed> */
    public function explain(?PresenceEvent $event): array
    {
        if (null === $event) {
            return [
                'reason' => 'none',
                'detail' => 'No presence activity recorded yet.',
                'surface' => null,
                'permissionsUsed' => [],
            ];
        }

        return [
            'reason' => $event->reason(),
            'detail' => $event->detail(),
            'surface' => $event->surface()->value,
            'permissionsUsed' => $event->permissionsUsed(),
        ];
    }

    /** @return array<string, mixed> */
    public function workspace(PresenceWorkspace $workspace): array
    {
        return [
            'id' => $workspace->id()->value,
            'scopeKey' => $workspace->scopeKey(),
            'preferences' => $this->preferences($workspace->preferences()),
            'session' => $this->session($workspace->activeSession()),
        ];
    }

    /** @return array<string, mixed> */
    private function eventToArray(PresenceEvent $event): array
    {
        return [
            'id' => $event->id(),
            'label' => $event->label(),
            'surface' => $event->surface()->value,
            'reason' => $event->reason(),
            'detail' => $event->detail(),
            'recordedAt' => $event->recordedAt()->format(\DateTimeInterface::ATOM),
            'permissionsUsed' => $event->permissionsUsed(),
        ];
    }

    public function parseSurface(string $value): ?PresenceSurface
    {
        return PresenceSurface::tryFrom($value);
    }

    public function parseCapability(string $value): ?PresenceCapability
    {
        return PresenceCapability::tryFrom($value);
    }
}
