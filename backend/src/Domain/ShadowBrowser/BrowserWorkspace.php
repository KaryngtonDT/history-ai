<?php

declare(strict_types=1);

namespace App\Domain\ShadowBrowser;

use App\Domain\ShadowBrowser\Exception\InvalidShadowBrowserException;

final readonly class BrowserWorkspace
{
    public function __construct(
        private BrowserWorkspaceId $id,
        private string $scopeKey,
        private BrowserSessionCollection $sessions,
        private BrowserActivityCollection $activities,
        private BrowserSitePolicyCollection $sitePolicies,
        private ?string $activeSessionId,
        private ?BrowserContext $currentContext,
    ) {
        if ('' === trim($scopeKey)) {
            throw new InvalidShadowBrowserException('Browser workspace scope cannot be empty.');
        }
    }

    public static function create(
        ?BrowserWorkspaceId $id = null,
        string $scopeKey = 'default',
    ): self {
        return new self(
            $id ?? BrowserWorkspaceId::generate(),
            trim($scopeKey),
            BrowserSessionCollection::empty(),
            BrowserActivityCollection::empty(),
            BrowserSitePolicyCollection::empty(),
            null,
            null,
        );
    }

    public function id(): BrowserWorkspaceId
    {
        return $this->id;
    }

    public function scopeKey(): string
    {
        return $this->scopeKey;
    }

    public function sessions(): BrowserSessionCollection
    {
        return $this->sessions;
    }

    public function activities(): BrowserActivityCollection
    {
        return $this->activities;
    }

    public function sitePolicies(): BrowserSitePolicyCollection
    {
        return $this->sitePolicies;
    }

    public function activeSessionId(): ?string
    {
        return $this->activeSessionId;
    }

    public function currentContext(): ?BrowserContext
    {
        return $this->currentContext;
    }

    public function activeSession(): ?BrowserSession
    {
        if (null === $this->activeSessionId) {
            return null;
        }

        return $this->sessions->find($this->activeSessionId);
    }

    public function connect(?string $shadowSessionId = null): self
    {
        $session = BrowserSession::connect($this->scopeKey, $shadowSessionId);

        return $this->replace(
            sessions: $this->sessions->upsert($session),
            activeSessionId: $session->id(),
        );
    }

    public function disconnect(): self
    {
        $active = $this->activeSession();

        if (null === $active) {
            throw new InvalidShadowBrowserException('No active browser session to disconnect.');
        }

        return $this->replace(
            sessions: $this->sessions->upsert($active->disconnect()),
            activeSessionId: null,
            currentContext: null,
            clearContext: true,
        );
    }

    public function updateActiveTab(BrowserTab $tab): self
    {
        $active = $this->activeSession();

        if (null === $active) {
            throw new InvalidShadowBrowserException('No active browser session to update.');
        }

        return $this->replace(
            sessions: $this->sessions->upsert($active->touch($tab)),
        );
    }

    public function recordActivity(BrowserActivity $activity): self
    {
        return $this->replace(activities: $this->activities->append($activity));
    }

    public function updateContext(BrowserContext $context): self
    {
        return $this->replace(currentContext: $context);
    }

    public function updateSitePolicies(BrowserSitePolicyCollection $sitePolicies): self
    {
        return $this->replace(sitePolicies: $sitePolicies);
    }

    private function replace(
        ?BrowserSessionCollection $sessions = null,
        ?BrowserActivityCollection $activities = null,
        ?BrowserSitePolicyCollection $sitePolicies = null,
        ?string $activeSessionId = null,
        ?BrowserContext $currentContext = null,
        bool $clearActiveSession = false,
        bool $clearContext = false,
    ): self {
        return new self(
            $this->id,
            $this->scopeKey,
            $sessions ?? $this->sessions,
            $activities ?? $this->activities,
            $sitePolicies ?? $this->sitePolicies,
            $clearActiveSession ? null : ($activeSessionId ?? $this->activeSessionId),
            $clearContext ? null : ($currentContext ?? $this->currentContext),
        );
    }
}
