<?php

declare(strict_types=1);

namespace App\Infrastructure\Mobile;

use App\Domain\Mobile\MobileCapabilities;
use App\Domain\Mobile\MobileConnection;
use App\Domain\Mobile\MobileConnectionMode;
use App\Domain\Mobile\MobileDevice;
use App\Domain\Mobile\MobileDeviceCollection;
use App\Domain\Mobile\MobilePreferences;
use App\Domain\Mobile\MobileSession;
use App\Domain\Mobile\MobileSessionCollection;
use App\Domain\Mobile\MobileState;
use App\Domain\Mobile\MobileWorkspace;
use App\Domain\Mobile\MobileWorkspaceId;
use App\Domain\Mobile\Exception\InvalidMobileException;
use JsonException;

final class MobilePersistenceMapper
{
    /** @return array<string, mixed> */
    public function toArray(MobileWorkspace $workspace): array
    {
        return [
            'id' => $workspace->id()->value,
            'scopeKey' => $workspace->scopeKey(),
            'activeSessionId' => $workspace->activeSessionId(),
            'activeDeviceId' => $workspace->activeDeviceId(),
            'pushToken' => $workspace->pushToken(),
            'devices' => array_map($this->deviceToArray(...), $workspace->devices()->all()),
            'sessions' => array_map($this->sessionToArray(...), $workspace->sessions()->all()),
            'connection' => $this->connectionToArray($workspace->connection()),
            'preferences' => $this->preferencesToArray($workspace->preferences()),
        ];
    }

    public function fromJson(string $json): MobileWorkspace
    {
        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidMobileException('Stored mobile workspace is not valid JSON.', 0, $exception);
        }

        if (!is_array($decoded) || !is_string($decoded['id'] ?? null)) {
            throw new InvalidMobileException('Stored mobile workspace is invalid.');
        }

        return new MobileWorkspace(
            new MobileWorkspaceId($decoded['id']),
            is_string($decoded['scopeKey'] ?? null) ? $decoded['scopeKey'] : 'default',
            $this->devicesFromArray(is_array($decoded['devices'] ?? null) ? $decoded['devices'] : []),
            $this->sessionsFromArray(is_array($decoded['sessions'] ?? null) ? $decoded['sessions'] : []),
            $this->connectionFromArray(is_array($decoded['connection'] ?? null) ? $decoded['connection'] : []),
            $this->preferencesFromArray(is_array($decoded['preferences'] ?? null) ? $decoded['preferences'] : []),
            is_string($decoded['pushToken'] ?? null) ? $decoded['pushToken'] : null,
            is_string($decoded['activeSessionId'] ?? null) ? $decoded['activeSessionId'] : null,
            is_string($decoded['activeDeviceId'] ?? null) ? $decoded['activeDeviceId'] : null,
        );
    }

    /** @return array<string, mixed> */
    private function deviceToArray(MobileDevice $device): array
    {
        return [
            'deviceId' => $device->deviceId(),
            'platform' => $device->platform(),
            'name' => $device->name(),
            'capabilities' => [
                'voice' => $device->capabilities()->voice(),
                'watchCompanion' => $device->capabilities()->watchCompanion(),
                'notifications' => $device->capabilities()->notifications(),
                'secondBrain' => $device->capabilities()->secondBrain(),
            ],
            'registeredAt' => $device->registeredAt()->format(\DateTimeInterface::ATOM),
            'lastSeenAt' => $device->lastSeenAt()->format(\DateTimeInterface::ATOM),
        ];
    }

    /** @return array<string, mixed> */
    private function sessionToArray(MobileSession $session): array
    {
        return [
            'id' => $session->id(),
            'scopeKey' => $session->scopeKey(),
            'deviceId' => $session->deviceId(),
            'state' => $session->state()->value,
            'shadowSessionId' => $session->shadowSessionId(),
            'connectedAt' => $session->connectedAt()->format(\DateTimeInterface::ATOM),
            'lastActiveAt' => $session->lastActiveAt()->format(\DateTimeInterface::ATOM),
        ];
    }

    /** @return array<string, mixed> */
    private function connectionToArray(MobileConnection $connection): array
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
    private function preferencesToArray(MobilePreferences $preferences): array
    {
        return [
            'notificationsEnabled' => $preferences->notificationsEnabled(),
            'notificationFrequency' => $preferences->notificationFrequency(),
            'categories' => $preferences->categories(),
            'voiceEnabled' => $preferences->voiceEnabled(),
            'language' => $preferences->language(),
        ];
    }

    /** @param list<array<string, mixed>> $items */
    private function devicesFromArray(array $items): MobileDeviceCollection
    {
        $devices = [];

        foreach ($items as $item) {
            if (!is_string($item['deviceId'] ?? null) || !is_string($item['platform'] ?? null)) {
                continue;
            }

            $capabilities = is_array($item['capabilities'] ?? null)
                ? MobileCapabilities::fromArray($item['capabilities'])
                : MobileCapabilities::createDefault();

            $devices[] = new MobileDevice(
                $item['deviceId'],
                $item['platform'],
                is_string($item['name'] ?? null) ? $item['name'] : $item['platform'],
                $capabilities,
                new \DateTimeImmutable(is_string($item['registeredAt'] ?? null) ? $item['registeredAt'] : 'now'),
                new \DateTimeImmutable(is_string($item['lastSeenAt'] ?? null) ? $item['lastSeenAt'] : 'now'),
            );
        }

        return new MobileDeviceCollection($devices);
    }

    /** @param list<array<string, mixed>> $items */
    private function sessionsFromArray(array $items): MobileSessionCollection
    {
        $sessions = [];

        foreach ($items as $item) {
            if (!is_string($item['id'] ?? null) || !is_string($item['deviceId'] ?? null)) {
                continue;
            }

            $state = is_string($item['state'] ?? null)
                ? MobileState::tryFrom($item['state']) ?? MobileState::Disconnected
                : MobileState::Disconnected;

            $sessions[] = new MobileSession(
                $item['id'],
                is_string($item['scopeKey'] ?? null) ? $item['scopeKey'] : 'default',
                $item['deviceId'],
                $state,
                is_string($item['shadowSessionId'] ?? null) ? $item['shadowSessionId'] : null,
                new \DateTimeImmutable(is_string($item['connectedAt'] ?? null) ? $item['connectedAt'] : 'now'),
                new \DateTimeImmutable(is_string($item['lastActiveAt'] ?? null) ? $item['lastActiveAt'] : 'now'),
            );
        }

        return new MobileSessionCollection($sessions);
    }

    /** @param array<string, mixed> $data */
    private function connectionFromArray(array $data): MobileConnection
    {
        if ([] === $data) {
            return MobileConnection::createDefault();
        }

        $mode = is_string($data['mode'] ?? null)
            ? MobileConnectionMode::tryFrom($data['mode']) ?? MobileConnectionMode::Auto
            : MobileConnectionMode::Auto;

        return new MobileConnection(
            $mode,
            is_string($data['localhostUrl'] ?? null) ? $data['localhostUrl'] : 'http://127.0.0.1:8080',
            is_string($data['lanUrl'] ?? null) ? $data['lanUrl'] : 'http://192.168.1.100:8080',
            is_string($data['tailscaleUrl'] ?? null) ? $data['tailscaleUrl'] : 'http://lumen.tailnet:8080',
            is_array($data['homeWifiSsids'] ?? null)
                ? array_values(array_filter($data['homeWifiSsids'], 'is_string'))
                : [],
        );
    }

    /** @param array<string, mixed> $data */
    private function preferencesFromArray(array $data): MobilePreferences
    {
        if ([] === $data) {
            return MobilePreferences::createDefault();
        }

        return new MobilePreferences(
            (bool) ($data['notificationsEnabled'] ?? true),
            is_string($data['notificationFrequency'] ?? null) ? $data['notificationFrequency'] : 'daily',
            is_array($data['categories'] ?? null)
                ? array_values(array_filter($data['categories'], 'is_string'))
                : ['missions', 'revisions', 'server'],
            (bool) ($data['voiceEnabled'] ?? true),
            is_string($data['language'] ?? null) ? $data['language'] : 'en',
        );
    }
}
