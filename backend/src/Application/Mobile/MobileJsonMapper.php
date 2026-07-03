<?php

declare(strict_types=1);

namespace App\Application\Mobile;

use App\Domain\Mobile\MobileConnection;
use App\Domain\Mobile\MobileDevice;
use App\Domain\Mobile\MobilePreferences;
use App\Domain\Mobile\MobileSession;
use App\Domain\Mobile\MobileState;
use App\Domain\Mobile\MobileWorkspace;

final class MobileJsonMapper
{
    /** @return array<string, mixed> */
    public function profile(MobileWorkspace $workspace): array
    {
        $active = $workspace->activeSession();
        $device = null !== $workspace->activeDeviceId()
            ? $workspace->devices()->find($workspace->activeDeviceId())
            : null;

        return [
            'id' => $workspace->id()->value,
            'scopeKey' => $workspace->scopeKey(),
            'state' => null !== $active ? $active->state()->value : MobileState::Disconnected->value,
            'session' => $this->session($active),
            'device' => null !== $device ? $this->device($device) : null,
            'connection' => $this->connection($workspace->connection()),
            'preferences' => $this->preferences($workspace->preferences()),
            'capabilities' => null !== $device ? $this->capabilities($device->capabilities()) : null,
        ];
    }

    /** @param array<string, mixed> $today */
    public function today(MobileWorkspace $workspace, array $today): array
    {
        return [
            'scopeKey' => $workspace->scopeKey(),
            'state' => null !== $workspace->activeSession()
                ? $workspace->activeSession()->state()->value
                : MobileState::Disconnected->value,
            ...$today,
        ];
    }

    /** @param array<string, mixed> $missions */
    public function missions(MobileWorkspace $workspace, array $missions): array
    {
        return [
            'scopeKey' => $workspace->scopeKey(),
            ...$missions,
        ];
    }

    /** @param array<string, mixed> $revisions */
    public function revisions(MobileWorkspace $workspace, array $revisions): array
    {
        return [
            'scopeKey' => $workspace->scopeKey(),
            ...$revisions,
        ];
    }

    /** @return array<string, mixed> */
    public function health(MobileWorkspace $workspace, array $health): array
    {
        return [
            'scopeKey' => $workspace->scopeKey(),
            'connectionMode' => $workspace->connection()->mode()->value,
            ...$health,
        ];
    }

    /** @return array<string, mixed> */
    public function server(MobileWorkspace $workspace, array $server): array
    {
        return [
            'scopeKey' => $workspace->scopeKey(),
            ...$server,
        ];
    }

    /** @return array<string, mixed> */
    public function connections(MobileWorkspace $workspace): array
    {
        return [
            'scopeKey' => $workspace->scopeKey(),
            'connection' => $this->connection($workspace->connection()),
            'activeDeviceId' => $workspace->activeDeviceId(),
            'state' => null !== $workspace->activeSession()
                ? $workspace->activeSession()->state()->value
                : MobileState::Disconnected->value,
        ];
    }

    /** @return array<string, mixed> */
    public function device(MobileDevice $device): array
    {
        return [
            'deviceId' => $device->deviceId(),
            'platform' => $device->platform(),
            'name' => $device->name(),
            'capabilities' => $this->capabilities($device->capabilities()),
            'registeredAt' => $device->registeredAt()->format(\DateTimeInterface::ATOM),
            'lastSeenAt' => $device->lastSeenAt()->format(\DateTimeInterface::ATOM),
        ];
    }

    /** @return array<string, mixed> */
    public function preferences(MobilePreferences $preferences): array
    {
        return [
            'notificationsEnabled' => $preferences->notificationsEnabled(),
            'notificationFrequency' => $preferences->notificationFrequency(),
            'categories' => $preferences->categories(),
            'voiceEnabled' => $preferences->voiceEnabled(),
            'language' => $preferences->language(),
        ];
    }

    /** @return array<string, mixed> */
    public function connection(MobileConnection $connection): array
    {
        return [
            'mode' => $connection->mode()->value,
            'localhostUrl' => $connection->localhostUrl(),
            'lanUrl' => $connection->lanUrl(),
            'tailscaleUrl' => $connection->tailscaleUrl(),
            'homeWifiSsids' => $connection->homeWifiSsids(),
        ];
    }

    /** @return array<string, mixed> */
    public function workspace(MobileWorkspace $workspace): array
    {
        return [
            'id' => $workspace->id()->value,
            'scopeKey' => $workspace->scopeKey(),
            'session' => $this->session($workspace->activeSession()),
            'device' => $this->activeDevice($workspace),
            'connection' => $this->connection($workspace->connection()),
            'preferences' => $this->preferences($workspace->preferences()),
        ];
    }

    /** @return array<string, mixed> */
    private function session(?MobileSession $session): array
    {
        if (null === $session) {
            return [
                'active' => false,
                'session' => null,
            ];
        }

        return [
            'active' => MobileState::Connected === $session->state(),
            'session' => [
                'id' => $session->id(),
                'scopeKey' => $session->scopeKey(),
                'deviceId' => $session->deviceId(),
                'state' => $session->state()->value,
                'shadowSessionId' => $session->shadowSessionId(),
                'connectedAt' => $session->connectedAt()->format(\DateTimeInterface::ATOM),
                'lastActiveAt' => $session->lastActiveAt()->format(\DateTimeInterface::ATOM),
            ],
        ];
    }

    /** @return array<string, mixed>|null */
    private function activeDevice(MobileWorkspace $workspace): ?array
    {
        if (null === $workspace->activeDeviceId()) {
            return null;
        }

        $device = $workspace->devices()->find($workspace->activeDeviceId());

        return null !== $device ? $this->device($device) : null;
    }

    /** @return array<string, mixed> */
    private function capabilities(\App\Domain\Mobile\MobileCapabilities $capabilities): array
    {
        return [
            'voice' => $capabilities->voice(),
            'watchCompanion' => $capabilities->watchCompanion(),
            'notifications' => $capabilities->notifications(),
            'secondBrain' => $capabilities->secondBrain(),
        ];
    }
}
