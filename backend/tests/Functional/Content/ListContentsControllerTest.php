<?php

declare(strict_types=1);

namespace App\Tests\Functional\Content;

use App\Infrastructure\Persistence\Doctrine\Content\ContentRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ListContentsControllerTest extends WebTestCase
{
    public function testEmptyContentListReturnsEmptyArray(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request('GET', '/api/contents');

        self::assertResponseIsSuccessful();
        self::assertJsonStringEqualsJsonString('[]', $client->getResponse()->getContent());
    }

    public function testListContainsCreatedContent(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'POST',
            '/api/contents',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'title' => 'The Roman Empire',
                'sourceType' => 'youtube_url',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(201);
        $created = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

        $client->request('GET', '/api/contents');

        self::assertResponseIsSuccessful();

        $items = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertCount(1, $items);
        self::assertSame($created['id'], $items[0]['id']);
        self::assertSame('The Roman Empire', $items[0]['title']);
        self::assertSame('youtube_url', $items[0]['sourceType']);
        self::assertSame('draft', $items[0]['status']);
    }

    public function testResponseShapeIsStable(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'POST',
            '/api/contents',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'title' => 'Ancient Greece',
                'sourceType' => 'upload_pdf',
            ], JSON_THROW_ON_ERROR),
        );

        $client->request('GET', '/api/contents');

        $items = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('id', $items[0]);
        self::assertArrayHasKey('title', $items[0]);
        self::assertArrayHasKey('sourceType', $items[0]);
        self::assertArrayHasKey('status', $items[0]);
        self::assertArrayHasKey('createdAt', $items[0]);
        self::assertArrayHasKey('updatedAt', $items[0]);
        self::assertCount(6, $items[0]);
    }

    private function resetDatabaseSchema(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $metadata = $entityManager->getMetadataFactory()->getMetadataFor(ContentRecord::class);
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema([$metadata]);
        $schemaTool->createSchema([$metadata]);
    }
}
