<?php

declare(strict_types=1);

namespace App\Application\ShadowPresence;

use App\Domain\Shadow\ShadowSessionId;
use App\Domain\Shadow\ShadowSessionRepositoryInterface;
use App\Domain\ShadowPresence\Exception\InvalidShadowPresenceException;
use App\Domain\ShadowPresence\PresenceSession;

final class ConversationBridge
{
    public function __construct(
        private readonly ShadowSessionRepositoryInterface $shadowSessionRepository,
    ) {
    }

    public function resolveShadowSessionId(?string $shadowSessionId): ?string
    {
        if (null === $shadowSessionId || '' === trim($shadowSessionId)) {
            return null;
        }

        if (!ShadowSessionId::isValid($shadowSessionId)) {
            throw new InvalidShadowPresenceException('Shadow session id must be a valid UUID.');
        }

        $session = $this->shadowSessionRepository->findById(new ShadowSessionId($shadowSessionId));

        if (null === $session) {
            throw new InvalidShadowPresenceException('Shadow session not found.');
        }

        return $session->id()->value;
    }

    public function resolveConversationSessionId(?string $shadowSessionId): ?string
    {
        if (null === $shadowSessionId) {
            return null;
        }

        $shadowSession = $this->shadowSessionRepository->findById(new ShadowSessionId($shadowSessionId));

        return $shadowSession?->conversationId()?->value;
    }

    public function conversationSessionId(PresenceSession $session): ?string
    {
        $shadowSessionId = $session->shadowSessionId();

        if (null === $shadowSessionId) {
            return null;
        }

        $shadowSession = $this->shadowSessionRepository->findById(new ShadowSessionId($shadowSessionId));

        return $shadowSession?->conversationId()?->value;
    }

    public function linkSession(PresenceSession $session, string $shadowSessionId): PresenceSession
    {
        $validated = $this->resolveShadowSessionId($shadowSessionId);

        return $session->withShadowSessionId($validated);
    }
}
