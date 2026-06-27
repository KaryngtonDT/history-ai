<?php

declare(strict_types=1);

namespace App\Tests\Functional\Collection;

use App\Infrastructure\Persistence\Doctrine\Collection\CollectionRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ListCollectionsControllerTest extends WebTestCase
{
    public function testEmptyCollectionsReturnsEmptyArray(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request('GET', '/api/collections');

        self::assertResponseIsSuccessful();
        self::assertJsonStringEqualsJsonString('[]', $client->getResponse()->getContent());
    }

    public function testListContainsCreatedCollection(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'POST',
            '/api/collections',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => 'Ancient Rome',
                'description' => 'Resources about Roman history',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(201);
        $created = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

        $client->request('GET', '/api/collections');

        self::assertResponseIsSuccessful();

        $collections = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertCount(1, $collections);
        self::assertSame($created['id'], $collections[0]['id']);
        self::assertSame('Ancient Rome', $collections[0]['name']);
        self::assertSame('Resources about Roman history', $collections[0]['description']);
        self::assertArrayHasKey('createdAt', $collections[0]);
    }

    private function resetDatabaseSchema(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $metadata = $entityManager->getMetadataFactory()->getMetadataFor(CollectionRecord::class);
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema([$metadata]);
        $schemaTool->createSchema([$metadata]);
    }
}
