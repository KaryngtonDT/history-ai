<?php

declare(strict_types=1);

namespace App\Tests\Functional\Artifact;

use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\Processing\ProcessingJobId;
use App\Infrastructure\Persistence\Doctrine\Artifact\ArtifactRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ListArtifactsByContentControllerTest extends WebTestCase
{
    public function testValidRequestReturnsArtifacts(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $contentId = ContentId::generate();
        $repository = static::getContainer()->get(ArtifactRepositoryInterface::class);
        $repository->save(Artifact::create(
            ArtifactId::generate(),
            $contentId,
            ProcessingJobId::generate(),
            ArtifactType::Summary,
            ArtifactContent::fromString('Generated summary text'),
        ));
        $repository->save(Artifact::create(
            ArtifactId::generate(),
            $contentId,
            ProcessingJobId::generate(),
            ArtifactType::Quiz,
            ArtifactContent::fromString('Generated quiz text'),
        ));

        $client->request('GET', sprintf('/api/contents/%s/artifacts', $contentId->value));

        self::assertResponseStatusCodeSame(200);

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertCount(2, $response);
        self::assertSame($contentId->value, $response[0]['contentId']);
        self::assertSame('summary', $response[0]['type']);
        self::assertSame('Generated summary text', $response[0]['content']);
        self::assertArrayHasKey('id', $response[0]);
        self::assertArrayHasKey('processingJobId', $response[0]);
        self::assertArrayHasKey('createdAt', $response[0]);
        self::assertSame('quiz', $response[1]['type']);
    }

    public function testValidRequestWithNoArtifactsReturnsEmptyArray(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $contentId = ContentId::generate();

        $client->request('GET', sprintf('/api/contents/%s/artifacts', $contentId->value));

        self::assertResponseStatusCodeSame(200);
        self::assertJsonStringEqualsJsonString('[]', $client->getResponse()->getContent());
    }

    public function testInvalidContentIdReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/contents/not-a-valid-uuid/artifacts');

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
