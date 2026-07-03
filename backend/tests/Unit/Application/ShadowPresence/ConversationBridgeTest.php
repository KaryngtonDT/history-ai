<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\ShadowPresence;

use App\Application\ShadowPresence\ConversationBridge;
use App\Domain\Chat\ConversationId;
use App\Domain\Shadow\ShadowSession;
use App\Domain\Shadow\ShadowSessionId;
use App\Domain\ShadowPresence\Exception\InvalidShadowPresenceException;
use App\Domain\ShadowPresence\PresenceSession;
use App\Domain\ShadowPresence\PresenceSurface;
use App\Domain\Video\VideoId;
use App\Infrastructure\Shadow\InMemoryShadowSessionRepository;
use PHPUnit\Framework\TestCase;

final class ConversationBridgeTest extends TestCase
{
    public function testResolveConversationSessionIdReturnsLinkedConversation(): void
    {
        $repository = new InMemoryShadowSessionRepository();
        $shadowSessionId = ShadowSessionId::generate();
        $conversationId = ConversationId::generate();

        $repository->save(ShadowSession::start(
            $shadowSessionId,
            VideoId::generate(),
            'en',
            null,
            $conversationId,
        ));

        $bridge = new ConversationBridge($repository);

        self::assertSame(
            $conversationId->value,
            $bridge->resolveConversationSessionId($shadowSessionId->value),
        );
    }

    public function testLinkSessionValidatesShadowSessionExists(): void
    {
        $repository = new InMemoryShadowSessionRepository();
        $bridge = new ConversationBridge($repository);
        $presenceSession = PresenceSession::connect('default', PresenceSurface::Desktop);

        $this->expectException(InvalidShadowPresenceException::class);
        $bridge->linkSession($presenceSession, ShadowSessionId::generate()->value);
    }

    public function testResolveShadowSessionIdRejectsInvalidUuid(): void
    {
        $bridge = new ConversationBridge(new InMemoryShadowSessionRepository());

        $this->expectException(InvalidShadowPresenceException::class);
        $bridge->resolveShadowSessionId('not-a-uuid');
    }
}
