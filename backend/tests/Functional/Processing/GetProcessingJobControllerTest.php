<?php

declare(strict_types=1);

namespace App\Tests\Functional\Processing;

use App\Infrastructure\Persistence\Doctrine\Content\ContentRecord;
use App\Infrastructure\Persistence\Doctrine\Processing\ProcessingJobRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class GetProcessingJobControllerTest extends WebTestCase
{
    public function testValidRequestReturnsOk(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'POST',
            '/api/contents',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'title' => 'The Roman Empire',
                'sourceType' => 'upload_pdf',
            ], JSON_THROW_ON_ERROR),
        );

        $contentResponse = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

        $client->request(
            'POST',
            sprintf('/api/contents/%s/processing-jobs', $contentResponse['id']),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['type' => 'summary'], JSON_THROW_ON_ERROR),
        );

        $createResponse = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        $jobId = $createResponse['id'];

        $client->request('GET', sprintf('/api/processing-jobs/%s', $jobId));

        self::assertResponseStatusCodeSame(200);

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame($jobId, $response['id']);
        self::assertSame($contentResponse['id'], $response['contentId']);
        self::assertSame('summary', $response['type']);
        self::assertSame('pending', $response['status']);
        self::assertSame(0, $response['progress']);
        self::assertNull($response['startedAt']);
        self::assertNull($response['completedAt']);
        self::assertNull($response['failedAt']);
    }

    public function testInvalidJobIdReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/processing-jobs/not-a-uuid');

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testUnknownJobIdReturnsNotFound(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request('GET', '/api/processing-jobs/550e8400-e29b-41d4-a716-446655440000');

        self::assertResponseStatusCodeSame(404);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Processing job not found"}',
            $client->getResponse()->getContent(),
        );
    }

    private function resetDatabaseSchema(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $metadataFactory = $entityManager->getMetadataFactory();
        $metadata = [
            $metadataFactory->getMetadataFor(ContentRecord::class),
            $metadataFactory->getMetadataFor(ProcessingJobRecord::class),
        ];
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }
}
