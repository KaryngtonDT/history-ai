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

final class AskConversationChatStreamControllerTest extends WebTestCase
{
    private const string CONTENT_ID = '550e8400-e29b-41d4-a716-446655440000';
    private const string OTHER_CONTENT_ID = '550e8400-e29b-41d4-a716-446655440099';
    private const string CONVERSATION_ID = '550e8400-e29b-41d4-a716-446655440001';
    private const string MOCK_STREAMED_ANSWER = 'Mock answer based on retrieved context .';

    public function testPostCreatesMissingConversationAndStreams(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'POST',
            $this->conversationChatStreamUrl(self::CONTENT_ID, self::CONVERSATION_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => 'Why did Rome collapse?'], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(200);
        self::assertStringStartsWith(
            'text/event-stream',
            (string) $client->getResponse()->headers->get('Content-Type'),
        );

        $events = $this->parseSseEvents($client->getResponse()->getContent());
        $conversationEvent = $this->findEvent($events, 'conversation');
        $conversation = json_decode($conversationEvent['data'], true, flags: JSON_THROW_ON_ERROR);

        self::assertSame(self::CONVERSATION_ID, $conversation['conversation']['id']);
        self::assertSame(self::CONTENT_ID, $conversation['conversation']['contentId']);
        self::assertCount(2, $conversation['conversation']['messages']);
        self::assertSame(self::MOCK_STREAMED_ANSWER, $conversation['conversation']['messages'][1]['text']);

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
            $this->conversationChatStreamUrl(self::CONTENT_ID, self::CONVERSATION_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => 'Follow-up question'], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(200);

        $events = $this->parseSseEvents($client->getResponse()->getContent());
        $conversation = json_decode(
            $this->findEvent($events, 'conversation')['data'],
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        self::assertCount(4, $conversation['conversation']['messages']);
        self::assertSame('Earlier question', $conversation['conversation']['messages'][0]['text']);
        self::assertSame('Follow-up question', $conversation['conversation']['messages'][2]['text']);
        self::assertSame(self::MOCK_STREAMED_ANSWER, $conversation['conversation']['messages'][3]['text']);
    }

    public function testPostUsesAllSelectedDocuments(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $artifactRepository = static::getContainer()->get(ArtifactRepositoryInterface::class);
        $artifactRepository->save(Artifact::create(
            new ArtifactId('550e8400-e29b-41d4-a716-446655440002'),
            new ContentId(self::CONTENT_ID),
            ProcessingJobId::generate(),
            ArtifactType::Summary,
            ArtifactContent::fromString('## Roman Empire summary'),
        ));
        $artifactRepository->save(Artifact::create(
            new ArtifactId('550e8400-e29b-41d4-a716-446655440003'),
            new ContentId(self::OTHER_CONTENT_ID),
            ProcessingJobId::generate(),
            ArtifactType::Summary,
            ArtifactContent::fromString('## Byzantine Empire summary'),
        ));

        $repository = static::getContainer()->get(ConversationRepositoryInterface::class);
        $repository->save(
            Conversation::start(
                new ConversationId(self::CONVERSATION_ID),
                new ContentId(self::CONTENT_ID),
            )->addDocument(new ContentId(self::OTHER_CONTENT_ID)),
        );

        $client->request(
            'POST',
            $this->conversationChatStreamUrl(self::CONTENT_ID, self::CONVERSATION_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => '## Roman Empire summary'], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(200);

        $events = $this->parseSseEvents($client->getResponse()->getContent());
        $conversation = json_decode(
            $this->findEvent($events, 'conversation')['data'],
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        self::assertSame('Mock answer based on retrieved context [1][2].', $conversation['conversation']['messages'][1]['text']);
    }

    public function testPostRejectsRouteContentIdNotSelected(): void
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
            $this->conversationChatStreamUrl(self::CONTENT_ID, self::CONVERSATION_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => 'Why did Rome collapse?'], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testPostEmitsTokenEventsInOrder(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'POST',
            $this->conversationChatStreamUrl(self::CONTENT_ID, self::CONVERSATION_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => 'Why did Rome collapse?'], JSON_THROW_ON_ERROR),
        );

        $events = $this->parseSseEvents($client->getResponse()->getContent());
        $tokenEvents = array_values(array_filter(
            $events,
            static fn (array $event): bool => 'token' === $event['event'],
        ));

        self::assertSame(
            [
                ['index' => 0, 'text' => 'Mock '],
                ['index' => 1, 'text' => 'answer '],
                ['index' => 2, 'text' => 'based '],
                ['index' => 3, 'text' => 'on '],
                ['index' => 4, 'text' => 'retrieved '],
                ['index' => 5, 'text' => 'context '],
                ['index' => 6, 'text' => '.'],
            ],
            array_map(
                static fn (array $event): array => json_decode($event['data'], true, flags: JSON_THROW_ON_ERROR),
                $tokenEvents,
            ),
        );
    }

    public function testPostEmitsConversationEventBeforeDone(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'POST',
            $this->conversationChatStreamUrl(self::CONTENT_ID, self::CONVERSATION_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => 'Why did Rome collapse?'], JSON_THROW_ON_ERROR),
        );

        $events = $this->parseSseEvents($client->getResponse()->getContent());
        $eventNames = array_map(static fn (array $event): string => $event['event'], $events);

        self::assertContains('conversation', $eventNames);
        self::assertContains('done', $eventNames);
        self::assertLessThan(array_search('done', $eventNames, true), array_search('conversation', $eventNames, true));
    }

    public function testInvalidContentIdReturnsBadRequest(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'POST',
            $this->conversationChatStreamUrl('not-a-valid-uuid', self::CONVERSATION_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => 'Why did Rome collapse?'], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
    }

    public function testInvalidConversationIdReturnsBadRequest(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'POST',
            $this->conversationChatStreamUrl(self::CONTENT_ID, 'not-a-valid-uuid'),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => 'Why did Rome collapse?'], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
    }

    public function testInvalidQuestionReturnsBadRequest(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'POST',
            $this->conversationChatStreamUrl(self::CONTENT_ID, self::CONVERSATION_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => '   '], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
    }

    public function testExistingConversationChatEndpointRemainsUnchanged(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'POST',
            sprintf('/api/contents/%s/conversations/%s/chat', self::CONTENT_ID, self::CONVERSATION_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => 'Why did Rome collapse?'], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(200);
        self::assertStringStartsWith('application/json', (string) $client->getResponse()->headers->get('Content-Type'));

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('answer', $response);
        self::assertSame(MockChatProvider::MOCK_ANSWER, $response['answer']['answer']);
    }

    public function testExistingContentChatStreamEndpointRemainsUnchanged(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'POST',
            sprintf('/api/contents/%s/chat/stream', self::CONTENT_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => 'Why did Rome collapse?'], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(200);
        self::assertStringStartsWith('text/event-stream', (string) $client->getResponse()->headers->get('Content-Type'));

        $events = $this->parseSseEvents($client->getResponse()->getContent());
        self::assertNotContains('conversation', array_map(static fn (array $e): string => $e['event'], $events));
        self::assertContains('done', array_map(static fn (array $e): string => $e['event'], $events));
    }

    private function conversationChatStreamUrl(string $contentId, string $conversationId): string
    {
        return sprintf('/api/contents/%s/conversations/%s/chat/stream', $contentId, $conversationId);
    }

    /**
     * @param list<array{event: string, data: string}> $events
     *
     * @return array{event: string, data: string}
     */
    private function findEvent(array $events, string $name): array
    {
        foreach ($events as $event) {
            if ($name === $event['event']) {
                return $event;
            }
        }

        self::fail(sprintf('Missing SSE event: %s', $name));
    }

    /**
     * @return list<array{event: string, data: string}>
     */
    private function parseSseEvents(string $content): array
    {
        $events = [];
        $blocks = preg_split("/\r?\n\r?\n/", trim($content)) ?: [];

        foreach ($blocks as $block) {
            if ('' === $block) {
                continue;
            }

            $eventName = '';
            $data = '';

            foreach (preg_split("/\r?\n/", $block) as $line) {
                if (str_starts_with($line, 'event: ')) {
                    $eventName = substr($line, 7);
                }

                if (str_starts_with($line, 'data: ')) {
                    $data = substr($line, 6);
                }
            }

            $events[] = [
                'event' => $eventName,
                'data' => $data,
            ];
        }

        return $events;
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
