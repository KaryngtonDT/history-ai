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

final class GetKnowledgeGraphControllerTest extends WebTestCase
{
    public function testValidRequestReturnsGraphJson(): void
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

        $client->request('GET', sprintf('/api/contents/%s/graph', $contentId->value));

        self::assertResponseStatusCodeSame(200);

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('nodes', $response);
        self::assertArrayHasKey('edges', $response);
        self::assertSame(3, count($response['nodes']));
        self::assertTrue($this->containsNode($response['nodes'], $transcriptId->value, 'transcript', 'Transcript'));
        self::assertTrue($this->containsNode($response['nodes'], $summaryId->value, 'summary', 'Summary'));
        self::assertTrue($this->containsNode($response['nodes'], $quizId->value, 'quiz', 'Quiz'));
        self::assertTrue($this->containsEdge(
            $response['edges'],
            $summaryId->value,
            $transcriptId->value,
            'derived_from',
        ));
        self::assertTrue($this->containsEdge(
            $response['edges'],
            $quizId->value,
            $summaryId->value,
            'references',
        ));
        self::assertSame(
            count($response['nodes']),
            count($this->uniqueNodes($response['nodes'])),
        );
        self::assertSame(
            count($response['edges']),
            count($this->uniqueEdges($response['edges'])),
        );
    }

    public function testValidRequestWithNoArtifactsReturnsEmptyGraph(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $contentId = ContentId::generate();

        $client->request('GET', sprintf('/api/contents/%s/graph', $contentId->value));

        self::assertResponseStatusCodeSame(200);
        self::assertJsonStringEqualsJsonString(
            '{"nodes":[],"edges":[]}',
            $client->getResponse()->getContent(),
        );
    }

    public function testInvalidContentIdReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/contents/not-a-valid-uuid/graph');

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    /**
     * @param list<array{artifactId: string, type: string, title: string}> $nodes
     */
    private function containsNode(
        array $nodes,
        string $artifactId,
        string $type,
        string $title,
    ): bool {
        foreach ($nodes as $node) {
            if (
                $node['artifactId'] === $artifactId
                && $node['type'] === $type
                && $node['title'] === $title
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<array{sourceArtifactId: string, targetArtifactId: string, type: string}> $edges
     */
    private function containsEdge(
        array $edges,
        string $sourceId,
        string $targetId,
        string $type,
    ): bool {
        foreach ($edges as $edge) {
            if (
                $edge['sourceArtifactId'] === $sourceId
                && $edge['targetArtifactId'] === $targetId
                && $edge['type'] === $type
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<array{artifactId: string, type: string, title: string}> $nodes
     *
     * @return list<string>
     */
    private function uniqueNodes(array $nodes): array
    {
        return array_values(array_unique(array_map(
            static fn (array $node): string => $node['artifactId'],
            $nodes,
        )));
    }

    /**
     * @param list<array{sourceArtifactId: string, targetArtifactId: string, type: string}> $edges
     *
     * @return list<string>
     */
    private function uniqueEdges(array $edges): array
    {
        return array_values(array_unique(array_map(
            static fn (array $edge): string => sprintf(
                '%s->%s:%s',
                $edge['sourceArtifactId'],
                $edge['targetArtifactId'],
                $edge['type'],
            ),
            $edges,
        )));
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
