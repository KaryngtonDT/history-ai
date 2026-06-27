<?php

declare(strict_types=1);

namespace App\Tests\Functional\Library;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Content\ContentId;
use App\Domain\Library\LibraryItemId;
use App\Domain\Library\LibraryItemRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Library\LibraryItemRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AddLibraryItemControllerTest extends WebTestCase
{
    public function testValidRequestReturnsCreated(): void
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

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame([
            'id' => $response['id'],
            'type' => 'summary',
            'title' => 'Roman Empire Summary',
            'createdAt' => $response['createdAt'],
        ], $response);
        self::assertIsString($response['id']);
        self::assertIsString($response['createdAt']);

        $repository = static::getContainer()->get(LibraryItemRepositoryInterface::class);
        $item = $repository->findById(new LibraryItemId($response['id']));

        self::assertNotNull($item);
        self::assertSame($contentId, $item->contentId()->value);
        self::assertSame($artifactId, $item->artifactId()->value);
        self::assertSame('Roman Empire Summary', $item->title()->value);
    }

    public function testInvalidContentIdReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/library/items',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'contentId' => 'not-a-valid-uuid',
                'artifactId' => ArtifactId::generate()->value,
                'type' => 'summary',
                'title' => 'Roman Empire Summary',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testInvalidArtifactIdReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/library/items',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'contentId' => ContentId::generate()->value,
                'artifactId' => 'not-a-valid-uuid',
                'type' => 'summary',
                'title' => 'Roman Empire Summary',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testInvalidTypeReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/library/items',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'contentId' => ContentId::generate()->value,
                'artifactId' => ArtifactId::generate()->value,
                'type' => 'invalid_type',
                'title' => 'Roman Empire Summary',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testEmptyTitleReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/library/items',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'contentId' => ContentId::generate()->value,
                'artifactId' => ArtifactId::generate()->value,
                'type' => 'summary',
                'title' => '   ',
            ], JSON_THROW_ON_ERROR),
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
        $metadata = $entityManager->getMetadataFactory()->getMetadataFor(LibraryItemRecord::class);
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema([$metadata]);
        $schemaTool->createSchema([$metadata]);
    }
}
