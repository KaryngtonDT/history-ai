<?php

declare(strict_types=1);

namespace App\Domain\ShadowPresence;

final readonly class PresenceContext
{
    /** @param list<string> $explainability */
    public function __construct(
        private string $scopeKey,
        private PresenceSurface $surface,
        private string $identityLabel,
        private int $conceptCount,
        private ?string $activeMissionTitle,
        private ?string $executiveHint,
        private ?string $conversationSessionId,
        private array $explainability,
    ) {
    }

    public function scopeKey(): string
    {
        return $this->scopeKey;
    }

    public function surface(): PresenceSurface
    {
        return $this->surface;
    }

    public function identityLabel(): string
    {
        return $this->identityLabel;
    }

    public function conceptCount(): int
    {
        return $this->conceptCount;
    }

    public function activeMissionTitle(): ?string
    {
        return $this->activeMissionTitle;
    }

    public function executiveHint(): ?string
    {
        return $this->executiveHint;
    }

    public function conversationSessionId(): ?string
    {
        return $this->conversationSessionId;
    }

    /** @return list<string> */
    public function explainability(): array
    {
        return $this->explainability;
    }
}
