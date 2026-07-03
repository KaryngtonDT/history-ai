<?php

declare(strict_types=1);

namespace App\Domain\ShadowPresence;

use App\Domain\ShadowPresence\Exception\InvalidShadowPresenceException;

final readonly class PresenceWorkspace
{
    public function __construct(
        private PresenceWorkspaceId $id,
        private string $scopeKey,
        private PresencePreferences $preferences,
        private PresenceSessionCollection $sessions,
        private PresenceEventCollection $events,
        private ?string $activeSessionId,
    ) {
        if ('' === trim($scopeKey)) {
            throw new InvalidShadowPresenceException('Presence workspace scope cannot be empty.');
        }
    }

    public static function create(
        ?PresenceWorkspaceId $id = null,
        string $scopeKey = 'default',
    ): self {
        return new self(
            $id ?? PresenceWorkspaceId::generate(),
            trim($scopeKey),
            PresencePreferences::default(),
            PresenceSessionCollection::empty(),
            PresenceEventCollection::empty(),
            null,
        );
    }

    public function id(): PresenceWorkspaceId
    {
        return $this->id;
    }

    public function scopeKey(): string
    {
        return $this->scopeKey;
    }

    public function preferences(): PresencePreferences
    {
        return $this->preferences;
    }

    public function sessions(): PresenceSessionCollection
    {
        return $this->sessions;
    }

    public function events(): PresenceEventCollection
    {
        return $this->events;
    }

    public function activeSessionId(): ?string
    {
        return $this->activeSessionId;
    }

    public function activeSession(): ?PresenceSession
    {
        if (null === $this->activeSessionId) {
            return null;
        }

        $session = $this->sessions->find($this->activeSessionId);

        if (null === $session || PresenceState::Connected !== $session->state()) {
            return null;
        }

        return $session;
    }

    public function connect(PresenceSurface $surface, ?string $shadowSessionId = null): self
    {
        if (!$this->preferences->isSurfaceEnabled($surface)) {
            throw new InvalidShadowPresenceException(
                sprintf('Surface "%s" is not enabled in presence preferences.', $surface->value),
            );
        }

        $session = PresenceSession::connect($this->scopeKey, $surface, $shadowSessionId);

        return $this->replace(
            sessions: $this->sessions->upsert($session),
            activeSessionId: $session->id(),
        );
    }

    public function disconnect(): self
    {
        $active = $this->activeSession();

        if (null === $active) {
            throw new InvalidShadowPresenceException('No active presence session to disconnect.');
        }

        return $this->replace(
            sessions: $this->sessions->upsert($active->disconnect()),
            clearActiveSession: true,
        );
    }

    public function recordEvent(PresenceEvent $event): self
    {
        return $this->replace(events: $this->events->append($event));
    }

    public function updatePreferences(PresencePreferences $preferences): self
    {
        return $this->replace(preferences: $preferences);
    }

    private function replace(
        ?PresencePreferences $preferences = null,
        ?PresenceSessionCollection $sessions = null,
        ?PresenceEventCollection $events = null,
        ?string $activeSessionId = null,
        bool $clearActiveSession = false,
    ): self {
        return new self(
            $this->id,
            $this->scopeKey,
            $preferences ?? $this->preferences,
            $sessions ?? $this->sessions,
            $events ?? $this->events,
            $clearActiveSession ? null : ($activeSessionId ?? $this->activeSessionId),
        );
    }
}
