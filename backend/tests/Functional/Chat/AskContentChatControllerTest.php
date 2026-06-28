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

final class AskContentChatControllerTest extends WebTestCase
{
    public function testPostReturnsMockAnswerWithSources(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $contentId = ContentId::generate();
        $summaryId = new ArtifactId('550e8400-e29b-41d4-a716-446655440002');
        $queryText = 'Why did Rome collapse?';

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
            sprintf('/api/contents/%s/chat', $contentId->value),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => $queryText], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(200);

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('Mock answer based on retrieved context [1].', $response['answer']);
        self::assertNotSame([], $response['sources']);
        self::assertSame($summaryId->value, $response['sources'][0]['artifactId']);
        self::assertArrayHasKey('chunkId', $response['sources'][0]);
        self::assertArrayHasKey('text', $response['sources'][0]);
        self::assertArrayHasKey('score', $response['sources'][0]);
    }

    public function testPostWithEmptyContentReturnsMockAnswerWithEmptySources(): void
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
            '/api/contents/not-a-valid-uuid/chat',
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
            sprintf('/api/contents/%s/chat', ContentId::generate()->value),
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
            sprintf('/api/contents/%s/chat', ContentId::generate()->value),
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
            sprintf('/api/contents/%s/chat', ContentId::generate()->value),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => str_repeat('a', 2001)], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
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
