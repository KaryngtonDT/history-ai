<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowPresence;

use App\Domain\ShadowPresence\Exception\InvalidShadowPresenceException;
use App\Domain\ShadowPresence\PresenceCapability;
use App\Domain\ShadowPresence\PresenceEvent;
use App\Domain\ShadowPresence\PresenceEventCollection;
use App\Domain\ShadowPresence\PresencePermission;
use App\Domain\ShadowPresence\PresencePermissionCollection;
use App\Domain\ShadowPresence\PresencePreferences;
use App\Domain\ShadowPresence\PresenceSession;
use App\Domain\ShadowPresence\PresenceSessionCollection;
use App\Domain\ShadowPresence\PresenceState;
use App\Domain\ShadowPresence\PresenceSurface;
use App\Domain\ShadowPresence\PresenceWorkspace;
use App\Domain\ShadowPresence\PresenceWorkspaceId;
use JsonException;

final class ShadowPresencePersistenceMapper
{
    /** @return array<string, mixed> */
    public function toArray(PresenceWorkspace $workspace): array
    {
        return [
            'id' => $workspace->id()->value,
            'scopeKey' => $workspace->scopeKey(),
            'activeSessionId' => $workspace->activeSessionId(),
            'preferences' => $this->preferencesToArray($workspace->preferences()),
            'sessions' => array_map($this->sessionToArray(...), $workspace->sessions()->all()),
            'events' => array_map($this->eventToArray(...), $workspace->events()->all()),
        ];
    }

    public function fromJson(string $json): PresenceWorkspace
    {
        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidShadowPresenceException('Stored presence workspace is not valid JSON.', 0, $exception);
        }

        if (!is_array($decoded) || !is_string($decoded['id'] ?? null)) {
            throw new InvalidShadowPresenceException('Stored presence workspace is invalid.');
        }

        return new PresenceWorkspace(
            new PresenceWorkspaceId($decoded['id']),
            is_string($decoded['scopeKey'] ?? null) ? $decoded['scopeKey'] : 'default',
            $this->preferencesFromArray(is_array($decoded['preferences'] ?? null) ? $decoded['preferences'] : []),
            $this->sessionsFromArray(is_array($decoded['sessions'] ?? null) ? $decoded['sessions'] : []),
            $this->eventsFromArray(is_array($decoded['events'] ?? null) ? $decoded['events'] : []),
            is_string($decoded['activeSessionId'] ?? null) ? $decoded['activeSessionId'] : null,
        );
    }

    /** @return array<string, mixed> */
    private function preferencesToArray(PresencePreferences $preferences): array
    {
        return [
            'shortcuts' => $preferences->shortcuts(),
            'notifications' => $preferences->notifications(),
            'voiceEnabled' => $preferences->voiceEnabled(),
            'proactiveEnabled' => $preferences->proactiveEnabled(),
            'surfaceEnabled' => $preferences->surfaceEnabled(),
            'permissions' => array_map(
                static fn (PresencePermission $permission): array => [
                    'capability' => $permission->capability()->value,
                    'granted' => $permission->granted(),
                ],
                $preferences->permissions()->all(),
            ),
        ];
    }

    /** @return array<string, mixed> */
    private function sessionToArray(PresenceSession $session): array
    {
        return [
            'id' => $session->id(),
            'scopeKey' => $session->scopeKey(),
            'surface' => $session->surface()->value,
            'state' => $session->state()->value,
            'shadowSessionId' => $session->shadowSessionId(),
            'connectedAt' => $session->connectedAt()->format(\DateTimeInterface::ATOM),
            'lastActiveAt' => $session->lastActiveAt()->format(\DateTimeInterface::ATOM),
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

    /** @param array<string, mixed> $data */
    private function preferencesFromArray(array $data): PresencePreferences
    {
        if ([] === $data) {
            return PresencePreferences::default();
        }

        $permissions = [];

        foreach (is_array($data['permissions'] ?? null) ? $data['permissions'] : [] as $item) {
            if (!is_string($item['capability'] ?? null)) {
                continue;
            }

            $capability = PresenceCapability::tryFrom($item['capability']);

            if (null === $capability) {
                continue;
            }

            $permissions[] = new PresencePermission($capability, (bool) ($item['granted'] ?? false));
        }

        $permissionCollection = [] !== $permissions
            ? new PresencePermissionCollection($permissions)
            : PresencePreferences::defaultPermissions();

        $surfaceEnabled = PresencePreferences::default()->surfaceEnabled();

        if (is_array($data['surfaceEnabled'] ?? null)) {
            foreach ($data['surfaceEnabled'] as $key => $enabled) {
                if (is_string($key)) {
                    $surfaceEnabled[$key] = (bool) $enabled;
                }
            }
        }

        return new PresencePreferences(
            is_string($data['shortcuts'] ?? null) ? $data['shortcuts'] : 'Ctrl+Shift+Space',
            (bool) ($data['notifications'] ?? true),
            (bool) ($data['voiceEnabled'] ?? false),
            (bool) ($data['proactiveEnabled'] ?? false),
            $surfaceEnabled,
            $permissionCollection,
        );
    }

    /** @param list<array<string, mixed>> $items */
    private function sessionsFromArray(array $items): PresenceSessionCollection
    {
        $sessions = [];

        foreach ($items as $item) {
            if (!is_string($item['id'] ?? null) || !is_string($item['surface'] ?? null)) {
                continue;
            }

            $surface = PresenceSurface::tryFrom($item['surface']);
            $state = is_string($item['state'] ?? null)
                ? PresenceState::tryFrom($item['state']) ?? PresenceState::Disconnected
                : PresenceState::Disconnected;

            if (null === $surface) {
                continue;
            }

            $sessions[] = new PresenceSession(
                $item['id'],
                is_string($item['scopeKey'] ?? null) ? $item['scopeKey'] : 'default',
                $surface,
                $state,
                is_string($item['shadowSessionId'] ?? null) ? $item['shadowSessionId'] : null,
                new \DateTimeImmutable(is_string($item['connectedAt'] ?? null) ? $item['connectedAt'] : 'now'),
                new \DateTimeImmutable(is_string($item['lastActiveAt'] ?? null) ? $item['lastActiveAt'] : 'now'),
            );
        }

        return new PresenceSessionCollection($sessions);
    }

    /** @param list<array<string, mixed>> $items */
    private function eventsFromArray(array $items): PresenceEventCollection
    {
        $events = [];

        foreach ($items as $item) {
            if (!is_string($item['id'] ?? null) || !is_string($item['label'] ?? null)) {
                continue;
            }

            $surface = is_string($item['surface'] ?? null)
                ? PresenceSurface::tryFrom($item['surface']) ?? PresenceSurface::Web
                : PresenceSurface::Web;

            $events[] = new PresenceEvent(
                $item['id'],
                $item['label'],
                $surface,
                is_string($item['reason'] ?? null) ? $item['reason'] : 'unknown',
                is_string($item['detail'] ?? null) ? $item['detail'] : '',
                new \DateTimeImmutable(is_string($item['recordedAt'] ?? null) ? $item['recordedAt'] : 'now'),
                is_array($item['permissionsUsed'] ?? null)
                    ? array_values(array_filter($item['permissionsUsed'], 'is_string'))
                    : [],
            );
        }

        return new PresenceEventCollection($events);
    }
}
