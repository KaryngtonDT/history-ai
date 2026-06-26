<?php

declare(strict_types=1);

namespace App\Tests\Functional\Processing;

use App\Infrastructure\Persistence\Doctrine\Content\ContentRecord;
use App\Infrastructure\Persistence\Doctrine\Processing\ProcessingJobRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class InternalProcessingJobControllerTest extends WebTestCase
{
    public function testWorkerLifecycleUpdatesProcessingJob(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $jobId = $this->createProcessingJobViaApi($client);

        $client->request('POST', sprintf('/internal/processing-jobs/%s/start', $jobId));
        self::assertResponseStatusCodeSame(204);

        $client->request(
            'POST',
            sprintf('/internal/processing-jobs/%s/progress', $jobId),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['progress' => 20], JSON_THROW_ON_ERROR),
        );
        self::assertResponseStatusCodeSame(204);

        $client->request(
            'POST',
            sprintf('/internal/processing-jobs/%s/progress', $jobId),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['progress' => 45], JSON_THROW_ON_ERROR),
        );
        self::assertResponseStatusCodeSame(204);

        $client->request(
            'POST',
            sprintf('/internal/processing-jobs/%s/progress', $jobId),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['progress' => 80], JSON_THROW_ON_ERROR),
        );
        self::assertResponseStatusCodeSame(204);

        $client->request('POST', sprintf('/internal/processing-jobs/%s/complete', $jobId));
        self::assertResponseStatusCodeSame(204);

        $client->request('GET', sprintf('/api/processing-jobs/%s', $jobId));
        self::assertResponseStatusCodeSame(200);

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('completed', $response['status']);
        self::assertSame(100, $response['progress']);
    }

    public function testStartReturnsNotFoundForUnknownJob(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'POST',
            '/internal/processing-jobs/550e8400-e29b-41d4-a716-446655440000/start',
        );

        self::assertResponseStatusCodeSame(404);
    }

    public function testStartReturnsBadRequestForInvalidJobId(): void
    {
        $client = static::createClient();

        $client->request('POST', '/internal/processing-jobs/not-a-uuid/start');

        self::assertResponseStatusCodeSame(400);
    }

    private function createProcessingJobViaApi(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client): string
    {
        $client->request(
            'POST',
            '/api/contents',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'title' => 'Worker Test',
                'sourceType' => 'upload_pdf',
            ], JSON_THROW_ON_ERROR),
        );
        self::assertResponseStatusCodeSame(201);
        $content = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

        $client->request(
            'POST',
            sprintf('/api/contents/%s/processing-jobs', $content['id']),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['type' => 'summary'], JSON_THROW_ON_ERROR),
        );
        self::assertResponseStatusCodeSame(201);

        $job = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

        return $job['id'];
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
