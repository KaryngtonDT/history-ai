<?php

declare(strict_types=1);

namespace App\Tests\Functional\Artifact;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Content\ContentId;
use App\Domain\Processing\ProcessingJobId;
use App\Infrastructure\Persistence\Doctrine\Artifact\ArtifactRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CreateArtifactInternalControllerTest extends WebTestCase
{
    public function testValidRequestReturnsCreated(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $contentId = ContentId::generate()->value;
        $processingJobId = ProcessingJobId::generate()->value;

        $client->request(
            'POST',
            '/internal/artifacts',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'contentId' => $contentId,
                'processingJobId' => $processingJobId,
                'type' => 'summary',
                'content' => 'Generated summary text',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(201);

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('id', $response);
        self::assertSame('summary', $response['type']);
        self::assertArrayHasKey('createdAt', $response);
        self::assertIsString($response['createdAt']);

        $repository = static::getContainer()->get(ArtifactRepositoryInterface::class);
        $artifact = $repository->findById(new ArtifactId($response['id']));

        self::assertNotNull($artifact);
        self::assertSame($contentId, $artifact->contentId()->value);
        self::assertSame($processingJobId, $artifact->processingJobId()->value);
        self::assertSame('Generated summary text', $artifact->content()->value());
    }

    public function testInvalidContentIdReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/internal/artifacts',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'contentId' => 'not-a-valid-uuid',
                'processingJobId' => ProcessingJobId::generate()->value,
                'type' => 'summary',
                'content' => 'Generated summary text',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testInvalidProcessingJobIdReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/internal/artifacts',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'contentId' => ContentId::generate()->value,
                'processingJobId' => 'not-a-valid-uuid',
                'type' => 'summary',
                'content' => 'Generated summary text',
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
            '/internal/artifacts',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'contentId' => ContentId::generate()->value,
                'processingJobId' => ProcessingJobId::generate()->value,
                'type' => 'invalid_type',
                'content' => 'Generated summary text',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testEmptyContentReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/internal/artifacts',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'contentId' => ContentId::generate()->value,
                'processingJobId' => ProcessingJobId::generate()->value,
                'type' => 'summary',
                'content' => '   ',
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
        $metadata = $entityManager->getMetadataFactory()->getMetadataFor(ArtifactRecord::class);
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema([$metadata]);
        $schemaTool->createSchema([$metadata]);
    }
}
