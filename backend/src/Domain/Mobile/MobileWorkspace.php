<?php

declare(strict_types=1);

namespace App\Domain\Mobile;

use App\Domain\Mobile\Exception\InvalidMobileException;

final readonly class MobileWorkspace
{
    public function __construct(
        private MobileWorkspaceId $id,
        private string $scopeKey,
        private MobileDeviceCollection $devices,
        private MobileSessionCollection $sessions,
        private MobileConnection $connection,
        private MobilePreferences $preferences,
        private ?string $pushToken,
        private ?string $activeSessionId,
        private ?string $activeDeviceId,
    ) {
        if ('' === trim($scopeKey)) {
            throw new InvalidMobileException('Mobile workspace scope cannot be empty.');
        }
    }

    public static function create(
        ?MobileWorkspaceId $id = null,
        string $scopeKey = 'default',
    ): self {
        return new self(
            $id ?? MobileWorkspaceId::generate(),
            trim($scopeKey),
            MobileDeviceCollection::empty(),
            MobileSessionCollection::empty(),
            MobileConnection::createDefault(),
            MobilePreferences::createDefault(),
            null,
            null,
            null,
        );
    }

    public function id(): MobileWorkspaceId
    {
        return $this->id;
    }

    public function scopeKey(): string
    {
        return $this->scopeKey;
    }

    public function devices(): MobileDeviceCollection
    {
        return $this->devices;
    }

    public function sessions(): MobileSessionCollection
    {
        return $this->sessions;
    }

    public function connection(): MobileConnection
    {
        return $this->connection;
    }

    public function preferences(): MobilePreferences
    {
        return $this->preferences;
    }

    public function pushToken(): ?string
    {
        return $this->pushToken;
    }

    public function activeSessionId(): ?string
    {
        return $this->activeSessionId;
    }

    public function activeDeviceId(): ?string
    {
        return $this->activeDeviceId;
    }

    public function activeSession(): ?MobileSession
    {
        if (null === $this->activeSessionId) {
            return null;
        }

        $session = $this->sessions->find($this->activeSessionId);

        if (null === $session || MobileState::Connected !== $session->state()) {
            return null;
        }

        return $session;
    }

    public function registerDevice(
        string $deviceId,
        string $platform,
        string $name,
        ?MobileCapabilities $capabilities = null,
    ): self {
        $existing = $this->devices->find($deviceId);

        $device = null !== $existing
            ? $existing->withName($name)->withCapabilities($capabilities ?? $existing->capabilities())
            : MobileDevice::register($deviceId, $platform, $name, $capabilities);

        return $this->replace(
            devices: $this->devices->upsert($device),
            activeDeviceId: $device->deviceId(),
        );
    }

    public function connectDevice(string $deviceId, ?string $shadowSessionId = null): self
    {
        $device = $this->devices->find($deviceId);

        if (null === $device) {
            throw new InvalidMobileException('Mobile device not registered.');
        }

        $session = MobileSession::connect($this->scopeKey, $deviceId, $shadowSessionId);

        return $this->replace(
            devices: $this->devices->upsert($device->touch()),
            sessions: $this->sessions->upsert($session),
            activeSessionId: $session->id(),
            activeDeviceId: $deviceId,
        );
    }

    public function sync(): self
    {
        $active = $this->activeSession();

        if (null === $active) {
            throw new InvalidMobileException('No active mobile session to sync.');
        }

        $device = $this->devices->find($active->deviceId());

        return $this->replace(
            sessions: $this->sessions->upsert($active->touch()),
            devices: null !== $device ? $this->devices->upsert($device->touch()) : $this->devices,
        );
    }

    /** @param array<string, mixed> $data */
    public function updateConnection(array $data): self
    {
        return $this->replace(connection: $this->connection->withUpdates($data));
    }

    /** @param array<string, mixed> $data */
    public function updatePreferences(array $data): self
    {
        return $this->replace(preferences: $this->preferences->withUpdates($data));
    }

    public function setPushToken(?string $pushToken): self
    {
        if (null !== $pushToken && '' === trim($pushToken)) {
            throw new InvalidMobileException('Push token cannot be empty when provided.');
        }

        return $this->replace(pushToken: $pushToken);
    }

    private function replace(
        ?MobileDeviceCollection $devices = null,
        ?MobileSessionCollection $sessions = null,
        ?MobileConnection $connection = null,
        ?MobilePreferences $preferences = null,
        ?string $pushToken = null,
        ?string $activeSessionId = null,
        ?string $activeDeviceId = null,
        bool $clearPushToken = false,
        bool $clearActiveSession = false,
        bool $clearActiveDevice = false,
    ): self {
        return new self(
            $this->id,
            $this->scopeKey,
            $devices ?? $this->devices,
            $sessions ?? $this->sessions,
            $connection ?? $this->connection,
            $preferences ?? $this->preferences,
            $clearPushToken ? null : ($pushToken ?? $this->pushToken),
            $clearActiveSession ? null : ($activeSessionId ?? $this->activeSessionId),
            $clearActiveDevice ? null : ($activeDeviceId ?? $this->activeDeviceId),
        );
    }
}
