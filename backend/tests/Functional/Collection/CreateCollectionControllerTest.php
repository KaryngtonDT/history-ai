<?php

declare(strict_types=1);

namespace App\Tests\Functional\Collection;

use App\Domain\Collection\CollectionId;
use App\Domain\Collection\CollectionRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Collection\CollectionRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CreateCollectionControllerTest extends WebTestCase
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

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame([
            'id' => $response['id'],
            'name' => 'Ancient Rome',
            'description' => 'Resources about Roman history',
            'createdAt' => $response['createdAt'],
        ], $response);
        self::assertIsString($response['id']);
        self::assertIsString($response['createdAt']);

        $repository = static::getContainer()->get(CollectionRepositoryInterface::class);
        $collection = $repository->findById(new CollectionId($response['id']));

        self::assertNotNull($collection);
        self::assertSame('Ancient Rome', $collection->name()->value);
        self::assertSame('Resources about Roman history', $collection->description()->value);
    }

    public function testEmptyNameReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/collections',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => '   ',
                'description' => 'Valid description',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testMissingNameReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/collections',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'description' => 'Valid description',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testEmptyDescriptionIsAccepted(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'POST',
            '/api/collections',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => 'Philosophy',
                'description' => '',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(201);

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('Philosophy', $response['name']);
        self::assertSame('', $response['description']);
    }

    public function testMissingDescriptionDefaultsToEmptyString(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'POST',
            '/api/collections',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => 'Languages',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(201);

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('Languages', $response['name']);
        self::assertSame('', $response['description']);
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
