<?php

declare(strict_types=1);

namespace App\Tests\Functional\Chat;

use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\Processing\ProcessingJobId;
use App\Infrastructure\Chat\MockChatProvider;
use App\Infrastructure\Persistence\Doctrine\Artifact\ArtifactRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AskContentChatStreamControllerTest extends WebTestCase
{
    public function testPostReturnsTextEventStream(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $contentId = ContentId::generate();

        $client->request(
            'POST',
            sprintf('/api/contents/%s/chat/stream', $contentId->value),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => 'Why did Rome collapse?'], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(200);
        self::assertStringStartsWith(
            'text/event-stream',
            (string) $client->getResponse()->headers->get('Content-Type'),
        );
    }

    public function testPostEmitsTokenEventsInOrder(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $contentId = ContentId::generate();

        $client->request(
            'POST',
            sprintf('/api/contents/%s/chat/stream', $contentId->value),
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

    public function testPostEmitsDoneEvent(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $contentId = ContentId::generate();

        $client->request(
            'POST',
            sprintf('/api/contents/%s/chat/stream', $contentId->value),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => 'Why did Rome collapse?'], JSON_THROW_ON_ERROR),
        );

        $events = $this->parseSseEvents($client->getResponse()->getContent());
        $doneEvents = array_values(array_filter(
            $events,
            static fn (array $event): bool => 'done' === $event['event'],
        ));

        self::assertCount(1, $doneEvents);
        self::assertSame('{}', $doneEvents[0]['data']);
    }

    public function testPostWithSourcesEmitsCitationToken(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $contentId = ContentId::generate();
        $summaryId = new ArtifactId('550e8400-e29b-41d4-a716-446655440002');

        $repository = static::getContainer()->get(ArtifactRepositoryInterface::class);
        $repository->save(Artifact::create(
            $summaryId,
            $contentId,
            ProcessingJobId::generate(),
            ArtifactType::Summary,
            ArtifactContent::fromString("## Ancient Rome\n753 BC — Foundation of Rome"),
        ));

        $client->request(
            'POST',
            sprintf('/api/contents/%s/chat/stream', $contentId->value),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => 'Why did Rome collapse?'], JSON_THROW_ON_ERROR),
        );

        $events = $this->parseSseEvents($client->getResponse()->getContent());
        $tokenEvents = array_values(array_filter(
            $events,
            static fn (array $event): bool => 'token' === $event['event'],
        ));
        $lastToken = json_decode(end($tokenEvents)['data'], true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('[1].', $lastToken['text']);
    }

    public function testExistingNonStreamingChatEndpointRemainsUnchanged(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $contentId = ContentId::generate();

        $client->request(
            'POST',
            sprintf('/api/contents/%s/chat', $contentId->value),
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
    }

    public function testInvalidContentIdReturnsBadRequest(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'POST',
            '/api/contents/not-a-valid-uuid/chat/stream',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => 'Why did Rome collapse?'], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testMissingQuestionReturnsBadRequest(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'POST',
            sprintf('/api/contents/%s/chat/stream', ContentId::generate()->value),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testEmptyQuestionReturnsBadRequest(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'POST',
            sprintf('/api/contents/%s/chat/stream', ContentId::generate()->value),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => '   '], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testTooLongQuestionReturnsBadRequest(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'POST',
            sprintf('/api/contents/%s/chat/stream', ContentId::generate()->value),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => str_repeat('a', 2001)], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
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
        $metadata = $entityManager->getMetadataFactory()->getMetadataFor(ArtifactRecord::class);
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema([$metadata]);
        $schemaTool->createSchema([$metadata]);
    }
}
