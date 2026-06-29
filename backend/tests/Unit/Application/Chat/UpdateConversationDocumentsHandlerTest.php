<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Chat;

use App\Application\Chat\Commands\UpdateConversationDocumentsCommand;
use App\Application\Chat\Handlers\UpdateConversationDocumentsHandler;
use App\Domain\Chat\ChatMessage;
use App\Domain\Chat\ChatMessageRole;
use App\Domain\Chat\Conversation;
use App\Domain\Chat\ConversationId;
use App\Domain\Chat\ConversationRepositoryInterface;
use App\Domain\Chat\Exception\ConversationNotFoundException;
use App\Domain\Content\ContentId;
use PHPUnit\Framework\TestCase;

final class UpdateConversationDocumentsHandlerTest extends TestCase
{
    private const string CONVERSATION_ID = '550e8400-e29b-41d4-a716-446655440001';
    private const string CONTENT_ID = '550e8400-e29b-41d4-a716-446655440000';
    private const string OTHER_CONTENT_ID = '550e8400-e29b-41d4-a716-446655440099';
    private const string THIRD_CONTENT_ID = '550e8400-e29b-41d4-a716-446655440088';

    public function testReplacesSelectedDocumentsAndPreservesMessages(): void
    {
        $conversationId = new ConversationId(self::CONVERSATION_ID);
        $existing = Conversation::start($conversationId, new ContentId(self::CONTENT_ID))
            ->appendUser(new ChatMessage(ChatMessageRole::User, 'Earlier question'))
            ->appendAssistant(new ChatMessage(ChatMessageRole::Assistant, 'Earlier answer'));
        $saved = null;

        $repository = $this->createMock(ConversationRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findById')
            ->with($conversationId)
            ->willReturn($existing);
        $repository
            ->expects(self::once())
            ->method('save')
            ->willReturnCallback(static function (Conversation $conversation) use (&$saved): void {
                $saved = $conversation;
            });

        $result = (new UpdateConversationDocumentsHandler($repository))
            ->__invoke(new UpdateConversationDocumentsCommand(
                self::CONVERSATION_ID,
                [self::OTHER_CONTENT_ID, self::THIRD_CONTENT_ID],
            ));

        self::assertNotNull($saved);
        self::assertSame(2, $saved->documents()->count());
        self::assertTrue($saved->containsDocument(new ContentId(self::OTHER_CONTENT_ID)));
        self::assertTrue($saved->containsDocument(new ContentId(self::THIRD_CONTENT_ID)));
        self::assertTrue($saved->contentId()->equals(new ContentId(self::OTHER_CONTENT_ID)));
        self::assertCount(2, $saved->messages());
        self::assertSame('Earlier question', $saved->messages()[0]->content());
        self::assertSame('Earlier answer', $saved->messages()[1]->content());

        self::assertSame(self::CONVERSATION_ID, $result->id);
        self::assertSame(self::OTHER_CONTENT_ID, $result->contentId);
        self::assertCount(2, $result->documents);
        self::assertSame(self::OTHER_CONTENT_ID, $result->documents[0]->contentId);
        self::assertSame(self::THIRD_CONTENT_ID, $result->documents[1]->contentId);
    }

    public function testDeduplicatesContentIdsPreservingOrder(): void
    {
        $conversationId = new ConversationId(self::CONVERSATION_ID);
        $existing = Conversation::start($conversationId, new ContentId(self::CONTENT_ID));

        $repository = $this->createMock(ConversationRepositoryInterface::class);
        $repository->method('findById')->willReturn($existing);
        $repository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (Conversation $conversation): bool {
                return 2 === $conversation->documents()->count()
                    && $conversation->documents()->all()[0]->contentId()->value === self::CONTENT_ID
                    && $conversation->documents()->all()[1]->contentId()->value === self::OTHER_CONTENT_ID;
            }));

        $result = (new UpdateConversationDocumentsHandler($repository))
            ->__invoke(new UpdateConversationDocumentsCommand(
                self::CONVERSATION_ID,
                [self::CONTENT_ID, self::OTHER_CONTENT_ID, self::CONTENT_ID],
            ));

        self::assertSame(
            [self::CONTENT_ID, self::OTHER_CONTENT_ID],
            array_map(
                static fn ($document): string => $document->contentId,
                $result->documents,
            ),
        );
    }

    public function testThrowsWhenConversationIsMissing(): void
    {
        $repository = $this->createMock(ConversationRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findById')
            ->willReturn(null);
        $repository->expects(self::never())->method('save');

        $this->expectException(ConversationNotFoundException::class);

        (new UpdateConversationDocumentsHandler($repository))
            ->__invoke(new UpdateConversationDocumentsCommand(self::CONVERSATION_ID, [self::CONTENT_ID]));
    }
}
