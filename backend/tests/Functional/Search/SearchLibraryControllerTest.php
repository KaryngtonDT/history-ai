<?php

declare(strict_types=1);

namespace App\Tests\Functional\Search;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Content\ContentId;
use App\Infrastructure\Persistence\Doctrine\Library\LibraryItemRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SearchLibraryControllerTest extends WebTestCase
{
    public function testSearchReturnsMatchingLibraryItems(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $contentId = ContentId::generate()->value;
        $artifactId = ArtifactId::generate()->value;

        $client->request(
            'POST',
            '/api/library/items',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'contentId' => $contentId,
                'artifactId' => $artifactId,
                'type' => 'summary',
                'title' => 'Roman Empire Summary',
            ], JSON_THROW_ON_ERROR),
        );
        self::assertResponseStatusCodeSame(201);

        $client->request('GET', '/api/search/library?q=roman');

        self::assertResponseIsSuccessful();

        $items = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertCount(1, $items);
        self::assertSame($contentId, $items[0]['contentId']);
        self::assertSame($artifactId, $items[0]['artifactId']);
        self::assertSame('summary', $items[0]['type']);
        self::assertSame('Roman Empire Summary', $items[0]['title']);
        self::assertArrayHasKey('id', $items[0]);
        self::assertArrayHasKey('createdAt', $items[0]);
    }

    public function testMissingQueryParameterReturnsBadRequest(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request('GET', '/api/search/library');

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testEmptyQueryParameterReturnsBadRequest(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request('GET', '/api/search/library?q=');

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testTooLongQueryParameterReturnsBadRequest(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request('GET', '/api/search/library?q='.str_repeat('a', 256));

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testSearchReturnsEmptyArrayWhenNoMatch(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'POST',
            '/api/library/items',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'contentId' => ContentId::generate()->value,
                'artifactId' => ArtifactId::generate()->value,
                'type' => 'summary',
                'title' => 'Roman Empire Summary',
            ], JSON_THROW_ON_ERROR),
        );
        self::assertResponseStatusCodeSame(201);

        $client->request('GET', '/api/search/library?q=byzantine');

        self::assertResponseIsSuccessful();
        self::assertJsonStringEqualsJsonString('[]', $client->getResponse()->getContent());
    }

    private function resetDatabaseSchema(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $metadata = $entityManager->getMetadataFactory()->getMetadataFor(LibraryItemRecord::class);
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema([$metadata]);
        $schemaTool->createSchema([$metadata]);
    }
}
