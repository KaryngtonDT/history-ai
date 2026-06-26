<?php

declare(strict_types=1);

namespace App\Tests\Functional\Processing;

use App\Domain\Content\ContentId;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Processing\ProcessingJobRepositoryInterface;
use App\Domain\Processing\ProcessingJobStatus;
use App\Infrastructure\Persistence\Doctrine\Content\ContentRecord;
use App\Infrastructure\Persistence\Doctrine\Processing\ProcessingJobRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CreateProcessingJobControllerTest extends WebTestCase
{
    public function testValidRequestReturnsCreated(): void
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

        self::assertResponseStatusCodeSame(201);
        $contentResponse = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        $contentId = $contentResponse['id'];

        $client->request(
            'POST',
            sprintf('/api/contents/%s/processing-jobs', $contentId),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['type' => 'summary'], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(201);

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame([
            'id' => $response['id'],
            'status' => 'pending',
            'progress' => 0,
        ], $response);
        self::assertIsString($response['id']);

        $repository = static::getContainer()->get(ProcessingJobRepositoryInterface::class);
        $job = $repository->findById(new ProcessingJobId($response['id']));

        self::assertNotNull($job);
        self::assertSame(ProcessingJobStatus::Pending, $job->status());
        self::assertTrue($job->contentId()->equals(new ContentId($contentId)));
    }

    public function testInvalidContentIdReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/contents/not-a-uuid/processing-jobs',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['type' => 'summary'], JSON_THROW_ON_ERROR),
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
        $this->resetDatabaseSchema();

        $contentId = $this->createContentViaApi($client);

        $client->request(
            'POST',
            sprintf('/api/contents/%s/processing-jobs', $contentId),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['type' => 'invalid_type'], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testMissingTypeReturnsBadRequest(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $contentId = $this->createContentViaApi($client);

        $client->request(
            'POST',
            sprintf('/api/contents/%s/processing-jobs', $contentId),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    private function createContentViaApi(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client): string
    {
        $client->request(
            'POST',
            '/api/contents',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'title' => 'Test Content',
                'sourceType' => 'upload_pdf',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(201);

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

        return $response['id'];
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
