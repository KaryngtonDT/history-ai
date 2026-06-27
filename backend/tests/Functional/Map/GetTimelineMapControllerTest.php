<?php

declare(strict_types=1);

namespace App\Tests\Functional\Map;

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

final class GetTimelineMapControllerTest extends WebTestCase
{
    public function testValidRequestReturnsTimelineMapJson(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $artifactId = ArtifactId::generate();
        $repository = static::getContainer()->get(ArtifactRepositoryInterface::class);
        $repository->save(Artifact::create(
            $artifactId,
            ContentId::generate(),
            ProcessingJobId::generate(),
            ArtifactType::Timeline,
            ArtifactContent::fromString(
                implode("\n", [
                    '# Timeline',
                    '',
                    '## Ancient Rome',
                    '- 753 BC — Foundation of Rome',
                    '- Trade with Athens',
                ]),
            ),
        ));

        $client->request('GET', sprintf('/api/maps/timeline/%s', $artifactId->value));

        self::assertResponseStatusCodeSame(200);

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(
            [
                'places' => [
                    [
                        'name' => 'Rome',
                        'coordinates' => [
                            'latitude' => 41.9028,
                            'longitude' => 12.4964,
                        ],
                        'description' => '753 BC — Foundation of Rome',
                    ],
                    [
                        'name' => 'Athens',
                        'coordinates' => [
                            'latitude' => 37.9838,
                            'longitude' => 23.7275,
                        ],
                        'description' => 'Trade with Athens',
                    ],
                ],
            ],
            $response,
        );
    }

    public function testEmptyTimelineReturnsEmptyPlacesArray(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $artifactId = ArtifactId::generate();
        $repository = static::getContainer()->get(ArtifactRepositoryInterface::class);
        $repository->save(Artifact::create(
            $artifactId,
            ContentId::generate(),
            ProcessingJobId::generate(),
            ArtifactType::Timeline,
            ArtifactContent::fromString(
                implode("\n", ['## Events', '- Battle of Teutoburg Forest']),
            ),
        ));

        $client->request('GET', sprintf('/api/maps/timeline/%s', $artifactId->value));

        self::assertResponseStatusCodeSame(200);
        self::assertJsonStringEqualsJsonString(
            '{"places":[]}',
            $client->getResponse()->getContent(),
        );
    }

    public function testMissingTimelineArtifactReturnsNotFound(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $artifactId = ArtifactId::generate();

        $client->request('GET', sprintf('/api/maps/timeline/%s', $artifactId->value));

        self::assertResponseStatusCodeSame(404);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Timeline artifact not found"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testNonTimelineArtifactReturnsNotFound(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $artifactId = ArtifactId::generate();
        $repository = static::getContainer()->get(ArtifactRepositoryInterface::class);
        $repository->save(Artifact::create(
            $artifactId,
            ContentId::generate(),
            ProcessingJobId::generate(),
            ArtifactType::Summary,
            ArtifactContent::fromString('Summary text'),
        ));

        $client->request('GET', sprintf('/api/maps/timeline/%s', $artifactId->value));

        self::assertResponseStatusCodeSame(404);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Timeline artifact not found"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testInvalidArtifactIdReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/maps/timeline/not-a-valid-uuid');

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
