<?php

declare(strict_types=1);

namespace App\Tests\Functional\Library;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Content\ContentId;
use App\Infrastructure\Persistence\Doctrine\Library\LibraryItemRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ListLibraryItemsControllerTest extends WebTestCase
{
    public function testEmptyLibraryReturnsEmptyArray(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request('GET', '/api/library/items');

        self::assertResponseIsSuccessful();
        self::assertJsonStringEqualsJsonString('[]', $client->getResponse()->getContent());
    }

    public function testListContainsCreatedLibraryItem(): void
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
        $created = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

        $client->request('GET', '/api/library/items');

        self::assertResponseIsSuccessful();

        $items = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertCount(1, $items);
        self::assertSame($created['id'], $items[0]['id']);
        self::assertSame($contentId, $items[0]['contentId']);
        self::assertSame($artifactId, $items[0]['artifactId']);
        self::assertSame('summary', $items[0]['type']);
        self::assertSame('Roman Empire Summary', $items[0]['title']);
        self::assertArrayHasKey('createdAt', $items[0]);
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
