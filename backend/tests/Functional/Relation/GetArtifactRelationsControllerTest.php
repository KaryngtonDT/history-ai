<?php

declare(strict_types=1);

namespace App\Tests\Functional\Relation;

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

final class GetArtifactRelationsControllerTest extends WebTestCase
{
    public function testValidRequestReturnsRelationsJson(): void
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

        $client->request('GET', sprintf('/api/contents/%s/relations', $contentId->value));

        self::assertResponseStatusCodeSame(200);

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('relations', $response);
        self::assertTrue($this->containsRelation(
            $response['relations'],
            $summaryId->value,
            $transcriptId->value,
            'derived_from',
        ));
        self::assertTrue($this->containsRelation(
            $response['relations'],
            $quizId->value,
            $summaryId->value,
            'references',
        ));
        self::assertSame(
            count($response['relations']),
            count($this->uniqueRelations($response['relations'])),
        );
    }

    public function testValidRequestWithNoArtifactsReturnsEmptyRelations(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $contentId = ContentId::generate();

        $client->request('GET', sprintf('/api/contents/%s/relations', $contentId->value));

        self::assertResponseStatusCodeSame(200);
        self::assertJsonStringEqualsJsonString(
            '{"relations":[]}',
            $client->getResponse()->getContent(),
        );
    }

    public function testInvalidContentIdReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/contents/not-a-valid-uuid/relations');

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    /**
     * @param list<array{sourceArtifactId: string, targetArtifactId: string, type: string}> $relations
     */
    private function containsRelation(
        array $relations,
        string $sourceId,
        string $targetId,
        string $type,
    ): bool {
        foreach ($relations as $relation) {
            if (
                $relation['sourceArtifactId'] === $sourceId
                && $relation['targetArtifactId'] === $targetId
                && $relation['type'] === $type
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<array{sourceArtifactId: string, targetArtifactId: string, type: string}> $relations
     *
     * @return list<string>
     */
    private function uniqueRelations(array $relations): array
    {
        return array_values(array_unique(array_map(
            static fn (array $relation): string => sprintf(
                '%s->%s:%s',
                $relation['sourceArtifactId'],
                $relation['targetArtifactId'],
                $relation['type'],
            ),
            $relations,
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
