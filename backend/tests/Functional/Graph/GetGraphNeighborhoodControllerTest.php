<?php

declare(strict_types=1);

namespace App\Tests\Functional\Graph;

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

final class GetGraphNeighborhoodControllerTest extends WebTestCase
{
    public function testValidRequestReturnsNeighborhoodJson(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $contentId = ContentId::generate();
        $transcriptId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $summaryId = new ArtifactId('550e8400-e29b-41d4-a716-446655440002');
        $quizId = new ArtifactId('550e8400-e29b-41d4-a716-446655440003');

        $repository = static::getContainer()->get(ArtifactRepositoryInterface::class);
        $repository->save(Artifact::create(
            $transcriptId,
            $contentId,
            ProcessingJobId::generate(),
            ArtifactType::Transcript,
            ArtifactContent::fromString('Transcript text'),
        ));
        $repository->save(Artifact::create(
            $summaryId,
            $contentId,
            ProcessingJobId::generate(),
            ArtifactType::Summary,
            ArtifactContent::fromString('Summary text'),
        ));
        $repository->save(Artifact::create(
            $quizId,
            $contentId,
            ProcessingJobId::generate(),
            ArtifactType::Quiz,
            ArtifactContent::fromString('Quiz text'),
        ));

        $client->request(
            'GET',
            sprintf(
                '/api/contents/%s/graph/artifacts/%s/neighborhood',
                $contentId->value,
                $summaryId->value,
            ),
        );

        self::assertResponseStatusCodeSame(200);

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('center', $response);
        self::assertArrayHasKey('neighbors', $response);
        self::assertArrayHasKey('edges', $response);
        self::assertSame($summaryId->value, $response['center']['artifactId']);
        self::assertSame('summary', $response['center']['type']);
        self::assertSame('Summary', $response['center']['label']);
        self::assertSame(2, count($response['neighbors']));
        self::assertTrue($this->containsNeighbor(
            $response['neighbors'],
            $transcriptId->value,
            'transcript',
            'Transcript',
        ));
        self::assertTrue($this->containsNeighbor(
            $response['neighbors'],
            $quizId->value,
            'quiz',
            'Quiz',
        ));
        self::assertTrue($this->containsEdge(
            $response['edges'],
            $summaryId->value,
            $transcriptId->value,
            'derived_from',
            1.0,
        ));
        self::assertTrue($this->containsEdge(
            $response['edges'],
            $quizId->value,
            $summaryId->value,
            'references',
            1.0,
        ));
    }

    public function testUnknownArtifactReturnsNotFound(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $contentId = ContentId::generate();
        $transcriptId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $unknownArtifactId = '550e8400-e29b-41d4-a716-446655440099';

        $repository = static::getContainer()->get(ArtifactRepositoryInterface::class);
        $repository->save(Artifact::create(
            $transcriptId,
            $contentId,
            ProcessingJobId::generate(),
            ArtifactType::Transcript,
            ArtifactContent::fromString('Transcript text'),
        ));

        $client->request(
            'GET',
            sprintf(
                '/api/contents/%s/graph/artifacts/%s/neighborhood',
                $contentId->value,
                $unknownArtifactId,
            ),
        );

        self::assertResponseStatusCodeSame(404);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Artifact not found"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testEmptyGraphWithUnknownArtifactReturnsNotFound(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $contentId = ContentId::generate();
        $unknownArtifactId = '550e8400-e29b-41d4-a716-446655440099';

        $client->request(
            'GET',
            sprintf(
                '/api/contents/%s/graph/artifacts/%s/neighborhood',
                $contentId->value,
                $unknownArtifactId,
            ),
        );

        self::assertResponseStatusCodeSame(404);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Artifact not found"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testInvalidContentIdReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/api/contents/not-a-valid-uuid/graph/artifacts/550e8400-e29b-41d4-a716-446655440001/neighborhood',
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

        $contentId = ContentId::generate();

        $client->request(
            'GET',
            sprintf(
                '/api/contents/%s/graph/artifacts/not-a-valid-uuid/neighborhood',
                $contentId->value,
            ),
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    /**
     * @param list<array{artifactId: string, type: string, label: string}> $neighbors
     */
    private function containsNeighbor(
        array $neighbors,
        string $artifactId,
        string $type,
        string $label,
    ): bool {
        foreach ($neighbors as $neighbor) {
            if (
                $neighbor['artifactId'] === $artifactId
                && $neighbor['type'] === $type
                && $neighbor['label'] === $label
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<array{
     *     sourceArtifactId: string,
     *     targetArtifactId: string,
     *     type: string,
     *     weight: float
     * }> $edges
     */
    private function containsEdge(
        array $edges,
        string $sourceId,
        string $targetId,
        string $type,
        float $weight,
    ): bool {
        foreach ($edges as $edge) {
            if (
                $edge['sourceArtifactId'] === $sourceId
                && $edge['targetArtifactId'] === $targetId
                && $edge['type'] === $type
                && (float) $edge['weight'] === $weight
            ) {
                return true;
            }
        }

        return false;
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
