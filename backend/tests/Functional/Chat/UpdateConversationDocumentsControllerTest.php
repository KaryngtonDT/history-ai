<?php

declare(strict_types=1);

namespace App\Tests\Functional\Chat;

use App\Domain\Chat\ChatMessage;
use App\Domain\Chat\ChatMessageRole;
use App\Domain\Chat\Conversation;
use App\Domain\Chat\ConversationId;
use App\Domain\Chat\ConversationRepositoryInterface;
use App\Domain\Content\ContentId;
use App\Infrastructure\Persistence\Doctrine\Chat\ConversationRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class UpdateConversationDocumentsControllerTest extends WebTestCase
{
    private const string CONTENT_ID = '550e8400-e29b-41d4-a716-446655440000';
    private const string OTHER_CONTENT_ID = '550e8400-e29b-41d4-a716-446655440099';
    private const string THIRD_CONTENT_ID = '550e8400-e29b-41d4-a716-446655440088';
    private const string CONVERSATION_ID = '550e8400-e29b-41d4-a716-446655440001';

    public function testPutUpdatesDocuments(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $repository = static::getContainer()->get(ConversationRepositoryInterface::class);
        $repository->save(
            Conversation::start(
                new ConversationId(self::CONVERSATION_ID),
                new ContentId(self::CONTENT_ID),
            )
                ->appendUser(new ChatMessage(ChatMessageRole::User, 'Earlier question'))
                ->appendAssistant(new ChatMessage(ChatMessageRole::Assistant, 'Earlier answer')),
        );

        $client->request(
            'PUT',
            $this->documentsUrl(self::CONVERSATION_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'contentIds' => [self::OTHER_CONTENT_ID, self::THIRD_CONTENT_ID],
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(200);

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(self::CONVERSATION_ID, $response['conversation']['id']);
        self::assertSame(self::OTHER_CONTENT_ID, $response['conversation']['contentId']);
        self::assertCount(2, $response['conversation']['documents']);
        self::assertSame(self::OTHER_CONTENT_ID, $response['conversation']['documents'][0]['contentId']);
        self::assertSame(self::THIRD_CONTENT_ID, $response['conversation']['documents'][1]['contentId']);
        self::assertCount(2, $response['conversation']['messages']);
        self::assertSame('Earlier question', $response['conversation']['messages'][0]['text']);
        self::assertSame('Earlier answer', $response['conversation']['messages'][1]['text']);

        $saved = $repository->findById(new ConversationId(self::CONVERSATION_ID));
        self::assertNotNull($saved);
        self::assertSame(2, $saved->documents()->count());
        self::assertCount(2, $saved->messages());
    }

    public function testPutDeduplicatesContentIdsPreservingOrder(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $repository = static::getContainer()->get(ConversationRepositoryInterface::class);
        $repository->save(
            Conversation::start(
                new ConversationId(self::CONVERSATION_ID),
                new ContentId(self::CONTENT_ID),
            ),
        );

        $client->request(
            'PUT',
            $this->documentsUrl(self::CONVERSATION_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'contentIds' => [
                    self::CONTENT_ID,
                    self::OTHER_CONTENT_ID,
                    self::CONTENT_ID,
                ],
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(200);

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(
            [self::CONTENT_ID, self::OTHER_CONTENT_ID],
            array_map(
                static fn (array $document): string => $document['contentId'],
                $response['conversation']['documents'],
            ),
        );
    }

    public function testPutRejectsEmptyContentIds(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $repository = static::getContainer()->get(ConversationRepositoryInterface::class);
        $repository->save(
            Conversation::start(
                new ConversationId(self::CONVERSATION_ID),
                new ContentId(self::CONTENT_ID),
            ),
        );

        $client->request(
            'PUT',
            $this->documentsUrl(self::CONVERSATION_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['contentIds' => []], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testPutRejectsInvalidContentId(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $repository = static::getContainer()->get(ConversationRepositoryInterface::class);
        $repository->save(
            Conversation::start(
                new ConversationId(self::CONVERSATION_ID),
                new ContentId(self::CONTENT_ID),
            ),
        );

        $client->request(
            'PUT',
            $this->documentsUrl(self::CONVERSATION_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['contentIds' => ['not-a-valid-uuid']], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testPutRejectsInvalidConversationId(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'PUT',
            $this->documentsUrl('not-a-valid-uuid'),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['contentIds' => [self::CONTENT_ID]], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testPutReturnsNotFoundForUnknownConversation(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'PUT',
            $this->documentsUrl(self::CONVERSATION_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['contentIds' => [self::CONTENT_ID]], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(404);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Conversation not found"}',
            $client->getResponse()->getContent(),
        );
    }

    private function documentsUrl(string $conversationId): string
    {
        return sprintf('/api/conversations/%s/documents', $conversationId);
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
