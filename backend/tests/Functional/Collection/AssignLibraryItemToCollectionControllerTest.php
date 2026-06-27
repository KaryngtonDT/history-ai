<?php

declare(strict_types=1);

namespace App\Tests\Functional\Collection;

use App\Domain\Collection\CollectionId;
use App\Domain\CollectionItem\CollectionItemRepositoryInterface;
use App\Domain\Library\LibraryItemId;
use App\Infrastructure\Persistence\Doctrine\Collection\CollectionRecord;
use App\Infrastructure\Persistence\Doctrine\CollectionItem\CollectionItemRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AssignLibraryItemToCollectionControllerTest extends WebTestCase
{
    public function testValidRequestReturnsCreated(): void
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
        $collection = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        $libraryItemId = LibraryItemId::generate()->value;

        $client->request(
            'POST',
            sprintf('/api/collections/%s/items', $collection['id']),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['libraryItemId' => $libraryItemId], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(201);

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame([
            'id' => $response['id'],
            'collectionId' => $collection['id'],
            'libraryItemId' => $libraryItemId,
            'createdAt' => $response['createdAt'],
        ], $response);
        self::assertIsString($response['id']);
        self::assertIsString($response['createdAt']);

        $repository = static::getContainer()->get(CollectionItemRepositoryInterface::class);
        self::assertTrue($repository->exists(
            new CollectionId($collection['id']),
            new LibraryItemId($libraryItemId),
        ));
    }

    public function testInvalidCollectionIdReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            sprintf(
                '/api/collections/%s/items',
                'not-a-valid-uuid',
            ),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'libraryItemId' => LibraryItemId::generate()->value,
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testInvalidLibraryItemIdReturnsBadRequest(): void
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
        $collection = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

        $client->request(
            'POST',
            sprintf('/api/collections/%s/items', $collection['id']),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['libraryItemId' => 'not-a-valid-uuid'], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testDuplicateAssignmentReturnsConflict(): void
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
        $collection = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        $libraryItemId = LibraryItemId::generate()->value;
        $url = sprintf('/api/collections/%s/items', $collection['id']);
        $body = json_encode(['libraryItemId' => $libraryItemId], JSON_THROW_ON_ERROR);

        $client->request(
            'POST',
            $url,
            server: ['CONTENT_TYPE' => 'application/json'],
            content: $body,
        );
        self::assertResponseStatusCodeSame(201);

        $client->request(
            'POST',
            $url,
            server: ['CONTENT_TYPE' => 'application/json'],
            content: $body,
        );

        self::assertResponseStatusCodeSame(409);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Library item already assigned to collection"}',
            $client->getResponse()->getContent(),
        );
    }

    private function resetDatabaseSchema(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $metadataFactory = $entityManager->getMetadataFactory();
        $metadata = [
            $metadataFactory->getMetadataFor(CollectionRecord::class),
            $metadataFactory->getMetadataFor(CollectionItemRecord::class),
        ];
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }
}
