<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Chat;

use App\Domain\Chat\ChatMessage;
use App\Domain\Chat\ChatMessageRole;
use App\Domain\Chat\Conversation;
use App\Domain\Chat\ConversationId;
use App\Domain\Chat\Exception\InvalidConversationDocumentException;
use App\Domain\Chat\Exception\InvalidConversationMessageException;
use App\Domain\Content\ContentId;
use PHPUnit\Framework\TestCase;

final class ConversationTest extends TestCase
{
    private const string CONVERSATION_ID = '550e8400-e29b-41d4-a716-446655440001';
    private const string CONTENT_ID = '550e8400-e29b-41d4-a716-446655440000';
    private const string OTHER_CONTENT_ID = '550e8400-e29b-41d4-a716-446655440099';
    private const string THIRD_CONTENT_ID = '550e8400-e29b-41d4-a716-446655440088';

    public function testStartCreatesConversationWithSingleDocument(): void
    {
        $conversation = $this->createConversation();

        self::assertTrue($conversation->id()->equals(new ConversationId(self::CONVERSATION_ID)));
        self::assertTrue($conversation->contentId()->equals(new ContentId(self::CONTENT_ID)));
        self::assertSame(1, $conversation->documents()->count());
        self::assertTrue($conversation->containsDocument(new ContentId(self::CONTENT_ID)));
        self::assertSame([], $conversation->messages());
    }

    public function testAddDocumentAppendsDocumentInOrder(): void
    {
        $conversation = $this->createConversation()
            ->addDocument(new ContentId(self::OTHER_CONTENT_ID))
            ->addDocument(new ContentId(self::THIRD_CONTENT_ID));

        self::assertSame(3, $conversation->documents()->count());
        self::assertSame(
            [self::CONTENT_ID, self::OTHER_CONTENT_ID, self::THIRD_CONTENT_ID],
            array_map(
                static fn ($document): string => $document->contentId()->value,
                $conversation->documents()->all(),
            ),
        );
    }

    public function testAddDocumentIgnoresDuplicate(): void
    {
        $conversation = $this->createConversation()
            ->addDocument(new ContentId(self::OTHER_CONTENT_ID))
            ->addDocument(new ContentId(self::OTHER_CONTENT_ID));

        self::assertSame(2, $conversation->documents()->count());
    }

    public function testRemoveDocumentRemovesSelectedDocument(): void
    {
        $conversation = $this->createConversation()
            ->addDocument(new ContentId(self::OTHER_CONTENT_ID))
            ->removeDocument(new ContentId(self::OTHER_CONTENT_ID));

        self::assertSame(1, $conversation->documents()->count());
        self::assertTrue($conversation->containsDocument(new ContentId(self::CONTENT_ID)));
        self::assertFalse($conversation->containsDocument(new ContentId(self::OTHER_CONTENT_ID)));
    }

    public function testRemoveLastDocumentThrows(): void
    {
        $this->expectException(InvalidConversationDocumentException::class);

        $this->createConversation()->removeDocument(new ContentId(self::CONTENT_ID));
    }

    public function testAppendUserAddsUserMessage(): void
    {
        $conversation = $this->createConversation()
            ->appendUser(new ChatMessage(ChatMessageRole::User, 'Why did Rome fall?'));

        self::assertCount(1, $conversation->messages());
        self::assertSame(ChatMessageRole::User, $conversation->messages()[0]->role());
        self::assertSame('Why did Rome fall?', $conversation->messages()[0]->content());
    }

    public function testAppendAssistantAddsAssistantMessage(): void
    {
        $conversation = $this->createConversation()
            ->appendUser(new ChatMessage(ChatMessageRole::User, 'Why did Rome fall?'))
            ->appendAssistant(new ChatMessage(ChatMessageRole::Assistant, 'Several factors contributed.'));

        self::assertCount(2, $conversation->messages());
        self::assertSame(ChatMessageRole::Assistant, $conversation->messages()[1]->role());
        self::assertSame('Several factors contributed.', $conversation->messages()[1]->content());
    }

    public function testMessagesRemainInChronologicalOrder(): void
    {
        $conversation = $this->createConversation()
            ->appendUser(new ChatMessage(ChatMessageRole::User, 'First question'))
            ->appendAssistant(new ChatMessage(ChatMessageRole::Assistant, 'First answer'))
            ->appendUser(new ChatMessage(ChatMessageRole::User, 'Follow-up question'))
            ->appendAssistant(new ChatMessage(ChatMessageRole::Assistant, 'Follow-up answer'));

        self::assertSame(
            [
                'First question',
                'First answer',
                'Follow-up question',
                'Follow-up answer',
            ],
            array_map(
                static fn (ChatMessage $message): string => $message->content(),
                $conversation->messages(),
            ),
        );
        self::assertSame(
            [
                ChatMessageRole::User,
                ChatMessageRole::Assistant,
                ChatMessageRole::User,
                ChatMessageRole::Assistant,
            ],
            array_map(
                static fn (ChatMessage $message): ChatMessageRole => $message->role(),
                $conversation->messages(),
            ),
        );
    }

    public function testOriginalConversationIsUnchangedAfterAppend(): void
    {
        $original = $this->createConversation();
        $extended = $original
            ->appendUser(new ChatMessage(ChatMessageRole::User, 'Question?'));

        self::assertSame([], $original->messages());
        self::assertCount(1, $extended->messages());
    }

    public function testOriginalConversationIsUnchangedAfterDocumentMutation(): void
    {
        $original = $this->createConversation();
        $extended = $original->addDocument(new ContentId(self::OTHER_CONTENT_ID));

        self::assertSame(1, $original->documents()->count());
        self::assertSame(2, $extended->documents()->count());
    }

    public function testAppendUserRejectsAssistantMessage(): void
    {
        $this->expectException(InvalidConversationMessageException::class);

        $this->createConversation()
            ->appendUser(new ChatMessage(ChatMessageRole::Assistant, 'Wrong role'));
    }

    public function testAppendAssistantRejectsUserMessage(): void
    {
        $this->expectException(InvalidConversationMessageException::class);

        $this->createConversation()
            ->appendAssistant(new ChatMessage(ChatMessageRole::User, 'Wrong role'));
    }

    private function createConversation(): Conversation
    {
        return Conversation::start(
            new ConversationId(self::CONVERSATION_ID),
            new ContentId(self::CONTENT_ID),
        );
    }
}
