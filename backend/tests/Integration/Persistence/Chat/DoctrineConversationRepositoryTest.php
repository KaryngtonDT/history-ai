<?php

declare(strict_types=1);

namespace App\Tests\Integration\Persistence\Chat;

use App\Domain\Chat\ChatMessage;
use App\Domain\Chat\ChatMessageRole;
use App\Domain\Chat\Conversation;
use App\Domain\Chat\ConversationId;
use App\Domain\Chat\ConversationRepositoryInterface;
use App\Domain\Content\ContentId;
use App\Infrastructure\Persistence\Doctrine\Chat\ConversationRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineConversationRepositoryTest extends KernelTestCase
{
    private const string CONTENT_ID = '550e8400-e29b-41d4-a716-446655440000';
    private const string OTHER_CONTENT_ID = '550e8400-e29b-41d4-a716-446655440099';

    private ConversationRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->resetDatabaseSchema();
        $this->repository = static::getContainer()->get(ConversationRepositoryInterface::class);
    }

    public function testSaveAndFindById(): void
    {
        $conversationId = new ConversationId('550e8400-e29b-41d4-a716-446655440001');
        $conversation = Conversation::start($conversationId, new ContentId(self::CONTENT_ID))
            ->appendUser(new ChatMessage(ChatMessageRole::User, 'Why did Rome fall?'))
            ->appendAssistant(new ChatMessage(ChatMessageRole::Assistant, 'Several factors contributed.'));

        $this->repository->save($conversation);

        $found = $this->repository->findById($conversationId);

        self::assertNotNull($found);
        self::assertTrue($found->id()->equals($conversationId));
        self::assertTrue($found->contentId()->equals(new ContentId(self::CONTENT_ID)));
        self::assertCount(2, $found->messages());
        self::assertSame('Why did Rome fall?', $found->messages()[0]->content());
        self::assertSame('Several factors contributed.', $found->messages()[1]->content());
    }

    public function testFindByIdReturnsNullWhenMissing(): void
    {
        self::assertNull($this->repository->findById(new ConversationId('550e8400-e29b-41d4-a716-446655440088')));
    }

    public function testSaveUpdatesExistingConversation(): void
    {
        $conversationId = new ConversationId('550e8400-e29b-41d4-a716-446655440002');
        $initial = Conversation::start($conversationId, new ContentId(self::CONTENT_ID))
            ->appendUser(new ChatMessage(ChatMessageRole::User, 'First question'));

        $this->repository->save($initial);

        $updated = $initial
            ->appendAssistant(new ChatMessage(ChatMessageRole::Assistant, 'First answer'))
            ->appendUser(new ChatMessage(ChatMessageRole::User, 'Follow-up question'));

        $this->repository->save($updated);

        $found = $this->repository->findById($conversationId);

        self::assertNotNull($found);
        self::assertCount(3, $found->messages());
        self::assertSame(
            [
                'First question',
                'First answer',
                'Follow-up question',
            ],
            array_map(
                static fn (ChatMessage $message): string => $message->content(),
                $found->messages(),
            ),
        );
    }

    public function testFindByContentIdReturnsConversationsForContent(): void
    {
        $older = Conversation::start(
            new ConversationId('550e8400-e29b-41d4-a716-446655440003'),
            new ContentId(self::CONTENT_ID),
        )->appendUser(new ChatMessage(ChatMessageRole::User, 'Older thread'));
        $newer = Conversation::start(
            new ConversationId('550e8400-e29b-41d4-a716-446655440004'),
            new ContentId(self::CONTENT_ID),
        )->appendUser(new ChatMessage(ChatMessageRole::User, 'Newer thread'));
        $otherContent = Conversation::start(
            new ConversationId('550e8400-e29b-41d4-a716-446655440005'),
            new ContentId(self::OTHER_CONTENT_ID),
        )->appendUser(new ChatMessage(ChatMessageRole::User, 'Different content'));

        $this->repository->save($older);
        $this->repository->save($newer);
        $this->repository->save(
            $newer->appendAssistant(new ChatMessage(ChatMessageRole::Assistant, 'Newer reply')),
        );
        $this->repository->save($otherContent);

        $found = $this->repository->findByContentId(new ContentId(self::CONTENT_ID));

        self::assertSame(2, $found->count());
        self::assertEqualsCanonicalizing(
            [
                '550e8400-e29b-41d4-a716-446655440003',
                '550e8400-e29b-41d4-a716-446655440004',
            ],
            array_map(
                static fn (Conversation $conversation): string => $conversation->id()->value,
                $found->conversations(),
            ),
        );
    }

    public function testFindByContentIdReturnsEmptyCollectionForUnknownContent(): void
    {
        $found = $this->repository->findByContentId(new ContentId(self::OTHER_CONTENT_ID));

        self::assertTrue($found->isEmpty());
        self::assertSame(0, $found->count());
        self::assertSame([], $found->conversations());
    }

    public function testPreservesMessageOrder(): void
    {
        $conversationId = new ConversationId('550e8400-e29b-41d4-a716-446655440006');
        $conversation = Conversation::start($conversationId, new ContentId(self::CONTENT_ID))
            ->appendUser(new ChatMessage(ChatMessageRole::User, 'One'))
            ->appendAssistant(new ChatMessage(ChatMessageRole::Assistant, 'Two'))
            ->appendUser(new ChatMessage(ChatMessageRole::User, 'Three'))
            ->appendAssistant(new ChatMessage(ChatMessageRole::Assistant, 'Four'));

        $this->repository->save($conversation);

        $found = $this->repository->findById($conversationId);

        self::assertNotNull($found);
        self::assertSame(
            [
                ChatMessageRole::User,
                ChatMessageRole::Assistant,
                ChatMessageRole::User,
                ChatMessageRole::Assistant,
            ],
            array_map(
                static fn (ChatMessage $message): ChatMessageRole => $message->role(),
                $found->messages(),
            ),
        );
        self::assertSame(
            ['One', 'Two', 'Three', 'Four'],
            array_map(
                static fn (ChatMessage $message): string => $message->content(),
                $found->messages(),
            ),
        );
    }

    private function resetDatabaseSchema(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $metadata = $entityManager->getMetadataFactory()->getMetadataFor(ConversationRecord::class);
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema([$metadata]);
        $schemaTool->createSchema([$metadata]);
    }
}
