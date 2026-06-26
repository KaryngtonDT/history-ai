<?php

declare(strict_types=1);

namespace App\Tests\Functional\Content;

use App\Domain\Content\ContentId;
use App\Domain\Content\ContentRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Content\ContentRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CreateContentControllerTest extends WebTestCase
{
    public function testCreatesContentSuccessfully(): void
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

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('id', $response);
        self::assertIsString($response['id']);

        $repository = static::getContainer()->get(ContentRepositoryInterface::class);
        $content = $repository->findById(new ContentId($response['id']));

        self::assertNotNull($content);
        self::assertSame('The Roman Empire', $content->title()->value);
    }

    public function testMissingTitleReturnsBadRequest(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/contents',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'sourceType' => 'upload_pdf',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testInvalidSourceTypeReturnsBadRequest(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/contents',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'title' => 'The Roman Empire',
                'sourceType' => 'invalid_source',
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
        $metadata = $entityManager->getMetadataFactory()->getMetadataFor(ContentRecord::class);
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema([$metadata]);
        $schemaTool->createSchema([$metadata]);
    }
}
