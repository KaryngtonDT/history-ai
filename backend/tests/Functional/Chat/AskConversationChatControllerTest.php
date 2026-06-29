<?php

declare(strict_types=1);

namespace App\Tests\Functional\Chat;

use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Chat\ChatMessage;
use App\Domain\Chat\ChatMessageRole;
use App\Domain\Chat\Conversation;
use App\Domain\Chat\ConversationId;
use App\Domain\Chat\ConversationRepositoryInterface;
use App\Domain\Content\ContentId;
use App\Domain\Processing\ProcessingJobId;
use App\Infrastructure\Chat\MockChatProvider;
use App\Infrastructure\Persistence\Doctrine\Artifact\ArtifactRecord;
use App\Infrastructure\Persistence\Doctrine\Chat\ConversationRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AskConversationChatControllerTest extends WebTestCase
{
    private const string CONTENT_ID = '550e8400-e29b-41d4-a716-446655440000';
    private const string OTHER_CONTENT_ID = '550e8400-e29b-41d4-a716-446655440099';
    private const string CONVERSATION_ID = '550e8400-e29b-41d4-a716-446655440001';

    public function testPostCreatesConversationWhenMissing(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'POST',
            $this->conversationChatUrl(self::CONTENT_ID, self::CONVERSATION_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => 'Why did Rome collapse?'], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(200);

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(self::CONVERSATION_ID, $response['conversation']['id']);
        self::assertSame(self::CONTENT_ID, $response['conversation']['contentId']);
        self::assertCount(2, $response['conversation']['messages']);
        self::assertSame('user', $response['conversation']['messages'][0]['role']);
        self::assertSame('Why did Rome collapse?', $response['conversation']['messages'][0]['text']);
        self::assertSame('assistant', $response['conversation']['messages'][1]['role']);
        self::assertSame(MockChatProvider::MOCK_ANSWER, $response['conversation']['messages'][1]['text']);
        self::assertSame(MockChatProvider::MOCK_ANSWER, $response['answer']['answer']);
        self::assertSame([], $response['answer']['sources']);
        self::assertSame([], $response['answer']['citations']);

        $repository = static::getContainer()->get(ConversationRepositoryInterface::class);
        $saved = $repository->findById(new ConversationId(self::CONVERSATION_ID));

        self::assertNotNull($saved);
        self::assertCount(2, $saved->messages());
    }

    public function testPostAppendsToExistingConversation(): void
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
            'POST',
            $this->conversationChatUrl(self::CONTENT_ID, self::CONVERSATION_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => 'Follow-up question'], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(200);

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertCount(4, $response['conversation']['messages']);
        self::assertSame('Earlier question', $response['conversation']['messages'][0]['text']);
        self::assertSame('Earlier answer', $response['conversation']['messages'][1]['text']);
        self::assertSame('Follow-up question', $response['conversation']['messages'][2]['text']);
        self::assertSame(MockChatProvider::MOCK_ANSWER, $response['conversation']['messages'][3]['text']);
    }

    public function testPostPreservesMessageOrder(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'POST',
            $this->conversationChatUrl(self::CONTENT_ID, self::CONVERSATION_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => 'First question'], JSON_THROW_ON_ERROR),
        );
        self::assertResponseStatusCodeSame(200);

        $client->request(
            'POST',
            $this->conversationChatUrl(self::CONTENT_ID, self::CONVERSATION_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => 'Second question'], JSON_THROW_ON_ERROR),
        );
        self::assertResponseStatusCodeSame(200);

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(
            ['First question', MockChatProvider::MOCK_ANSWER, 'Second question', MockChatProvider::MOCK_ANSWER],
            array_map(
                static fn (array $message): string => $message['text'],
                $response['conversation']['messages'],
            ),
        );
        self::assertSame(
            ['user', 'assistant', 'user', 'assistant'],
            array_map(
                static fn (array $message): string => $message['role'],
                $response['conversation']['messages'],
            ),
        );
    }

    public function testPostReturnsSourcesWhenArtifactsExist(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $contentId = new ContentId(self::CONTENT_ID);
        $summaryId = new ArtifactId('550e8400-e29b-41d4-a716-446655440002');
        $artifactRepository = static::getContainer()->get(ArtifactRepositoryInterface::class);
        $artifactRepository->save(Artifact::create(
            $summaryId,
            $contentId,
            ProcessingJobId::generate(),
            ArtifactType::Summary,
            ArtifactContent::fromString("## Ancient Rome\n753 BC — Foundation of Rome"),
        ));

        $client->request(
            'POST',
            $this->conversationChatUrl(self::CONTENT_ID, self::CONVERSATION_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => 'Why did Rome collapse?'], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(200);

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('Mock answer based on retrieved context [1].', $response['answer']['answer']);
        self::assertNotSame([], $response['answer']['sources']);
        self::assertCount(1, $response['answer']['citations']);
    }

    public function testPostRejectsConversationFromAnotherContent(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $repository = static::getContainer()->get(ConversationRepositoryInterface::class);
        $repository->save(
            Conversation::start(
                new ConversationId(self::CONVERSATION_ID),
                new ContentId(self::OTHER_CONTENT_ID),
            ),
        );

        $client->request(
            'POST',
            $this->conversationChatUrl(self::CONTENT_ID, self::CONVERSATION_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => 'Why did Rome collapse?'], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testInvalidContentIdReturnsBadRequest(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'POST',
            $this->conversationChatUrl('not-a-valid-uuid', self::CONVERSATION_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => 'Why did Rome collapse?'], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testInvalidConversationIdReturnsBadRequest(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'POST',
            $this->conversationChatUrl(self::CONTENT_ID, 'not-a-valid-uuid'),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => 'Why did Rome collapse?'], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testInvalidQuestionReturnsBadRequest(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'POST',
            $this->conversationChatUrl(self::CONTENT_ID, self::CONVERSATION_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => '   '], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testExistingChatEndpointRemainsUnchanged(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'POST',
            sprintf('/api/contents/%s/chat', self::CONTENT_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => 'Why did Rome collapse?'], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(200);
        self::assertJsonStringEqualsJsonString(
            json_encode([
                'answer' => MockChatProvider::MOCK_ANSWER,
                'sources' => [],
                'citations' => [],
            ], JSON_THROW_ON_ERROR),
            $client->getResponse()->getContent(),
        );

        $repository = static::getContainer()->get(ConversationRepositoryInterface::class);
        self::assertTrue($repository->findByContentId(new ContentId(self::CONTENT_ID))->isEmpty());
    }

    private function conversationChatUrl(string $contentId, string $conversationId): string
    {
        return sprintf('/api/contents/%s/conversations/%s/chat', $contentId, $conversationId);
    }

    private function resetDatabaseSchema(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $metadataFactory = $entityManager->getMetadataFactory();
        $metadata = [
            $metadataFactory->getMetadataFor(ArtifactRecord::class),
            $metadataFactory->getMetadataFor(ConversationRecord::class),
        ];
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }
}
