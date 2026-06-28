<?php

declare(strict_types=1);

namespace App\Tests\Functional\Semantic;

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

final class SearchSemanticChunksControllerTest extends WebTestCase
{
    public function testValidRequestReturnsSemanticResultsJson(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $contentId = ContentId::generate();
        $summaryId = new ArtifactId('550e8400-e29b-41d4-a716-446655440002');
        $timelineId = new ArtifactId('550e8400-e29b-41d4-a716-446655440004');

        $repository = static::getContainer()->get(ArtifactRepositoryInterface::class);
        $queryText = "## Ancient Rome\n753 BC — Foundation of Rome";
        $repository->save(Artifact::create(
            $summaryId,
            $contentId,
            ProcessingJobId::generate(),
            ArtifactType::Summary,
            ArtifactContent::fromString($queryText),
        ));
        $repository->save(Artifact::create(
            $timelineId,
            $contentId,
            ProcessingJobId::generate(),
            ArtifactType::Timeline,
            ArtifactContent::fromString("## Greek history\nClassical period overview"),
        ));

        $client->request(
            'GET',
            sprintf(
                '/api/contents/%s/semantic-search?q=%s',
                $contentId->value,
                urlencode($queryText),
            ),
        );

        self::assertResponseStatusCodeSame(200);

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('results', $response);
        self::assertNotSame([], $response['results']);
        self::assertSame($summaryId->value, $response['results'][0]['artifactId']);
        self::assertSame(0, $response['results'][0]['position']);
        self::assertSame($queryText, $response['results'][0]['text']);
        self::assertEquals(1.0, $response['results'][0]['score']);

        foreach ($response['results'] as $result) {
            self::assertArrayHasKey('artifactId', $result);
            self::assertArrayHasKey('chunkId', $result);
            self::assertArrayHasKey('position', $result);
            self::assertArrayHasKey('text', $result);
            self::assertArrayHasKey('score', $result);
            self::assertIsNumeric($result['score']);
            self::assertGreaterThanOrEqual(0.0, (float) $result['score']);
            self::assertLessThanOrEqual(1.0, (float) $result['score']);
        }

        $scores = array_column($response['results'], 'score');

        for ($index = 0; $index < count($scores) - 1; ++$index) {
            self::assertGreaterThanOrEqual($scores[$index + 1], $scores[$index]);
        }
    }

    public function testValidRequestWithNoArtifactsReturnsEmptyResults(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $contentId = ContentId::generate();

        $client->request(
            'GET',
            sprintf('/api/contents/%s/semantic-search?q=rome', $contentId->value),
        );

        self::assertResponseStatusCodeSame(200);
        self::assertJsonStringEqualsJsonString(
            '{"results":[]}',
            $client->getResponse()->getContent(),
        );
    }

    public function testInvalidContentIdReturnsBadRequest(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request('GET', '/api/contents/not-a-valid-uuid/semantic-search?q=rome');

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testMissingQueryParameterReturnsBadRequest(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'GET',
            sprintf('/api/contents/%s/semantic-search', ContentId::generate()->value),
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testEmptyQueryParameterReturnsBadRequest(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'GET',
            sprintf('/api/contents/%s/semantic-search?q=', ContentId::generate()->value),
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testTooLongQueryParameterReturnsBadRequest(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'GET',
            sprintf(
                '/api/contents/%s/semantic-search?q=%s',
                ContentId::generate()->value,
                str_repeat('a', 501),
            ),
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
