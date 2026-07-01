<?php

declare(strict_types=1);

namespace App\Application\Shadow;

use App\Domain\Chat\ConversationId;
use App\Domain\Content\ContentId;
use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Shadow\ShadowSession;
use App\Domain\Shadow\ShadowSessionId;
use App\Domain\Shadow\ShadowSessionRepositoryInterface;
use App\Domain\Shadow\ShadowTimestamp;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;

final class ShadowSessionResolver
{
    public function __construct(
        private readonly ShadowSessionRepositoryInterface $sessionRepository,
    ) {
    }

    public function resolve(string $videoId, string $sessionId): ShadowSession
    {
        try {
            $resolvedVideoId = new VideoId($videoId);
            $resolvedSessionId = new ShadowSessionId($sessionId);
        } catch (InvalidVideoIdException|InvalidShadowSessionException) {
            throw new InvalidShadowSessionException('Shadow session was not found.');
        }

        $session = $this->sessionRepository->findById($resolvedSessionId);

        if (null === $session || !$session->videoId()->equals($resolvedVideoId)) {
            throw new InvalidShadowSessionException('Shadow session was not found.');
        }

        return $session;
    }

    public function withOptionalTimestamp(
        ShadowSession $session,
        ?float $currentTimeSeconds,
    ): ShadowSession {
        if (null === $currentTimeSeconds) {
            return $session;
        }

        if ($currentTimeSeconds < 0) {
            throw new InvalidShadowSessionException('Shadow timestamp cannot be negative.');
        }

        return $session->withTimestamp(ShadowTimestamp::fromSeconds($currentTimeSeconds));
    }

    public function optionalContentId(?string $contentId): ?ContentId
    {
        if (null === $contentId || '' === trim($contentId)) {
            return null;
        }

        return new ContentId($contentId);
    }

    public function optionalConversationId(?string $conversationId): ?ConversationId
    {
        if (null === $conversationId || '' === trim($conversationId)) {
            return null;
        }

        return new ConversationId($conversationId);
    }
}
