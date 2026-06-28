<?php

declare(strict_types=1);

namespace App\Tests\Functional\Recommendation;

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

final class GetArtifactRecommendationsControllerTest extends WebTestCase
{
    public function testValidRequestReturnsRecommendationsJson(): void
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
                '/api/contents/%s/artifacts/%s/recommendations',
                $contentId->value,
                $summaryId->value,
            ),
        );

        self::assertResponseStatusCodeSame(200);

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('recommendations', $response);
        self::assertSame(2, count($response['recommendations']));
        self::assertSame(
            ['derived_from', 'references'],
            array_column($response['recommendations'], 'reason'),
        );
        self::assertSame(
            [$transcriptId->value, $quizId->value],
            array_column($response['recommendations'], 'artifactId'),
        );
        self::assertSame(
            [100, 80],
            array_column($response['recommendations'], 'score'),
        );
        foreach ($response['recommendations'] as $recommendation) {
            self::assertArrayHasKey('artifactId', $recommendation);
            self::assertArrayHasKey('type', $recommendation);
            self::assertArrayHasKey('title', $recommendation);
            self::assertArrayHasKey('reason', $recommendation);
            self::assertArrayHasKey('score', $recommendation);
            self::assertIsInt($recommendation['score']);
            self::assertGreaterThanOrEqual(0, $recommendation['score']);
            self::assertLessThanOrEqual(100, $recommendation['score']);
        }
        self::assertTrue($this->containsRecommendation(
            $response['recommendations'],
            $transcriptId->value,
            'transcript',
            'Transcript',
            'derived_from',
            100,
        ));
        self::assertTrue($this->containsRecommendation(
            $response['recommendations'],
            $quizId->value,
            'quiz',
            'Quiz',
            'references',
            80,
        ));
        self::assertFalse($this->containsRecommendation(
            $response['recommendations'],
            $summaryId->value,
            'summary',
            'Summary',
            'derived_from',
            100,
        ));
        self::assertSame(
            count($response['recommendations']),
            count($this->uniqueRecommendations($response['recommendations'])),
        );
    }

    public function testValidRequestWithNoArtifactsReturnsEmptyRecommendations(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $contentId = ContentId::generate();
        $artifactId = '550e8400-e29b-41d4-a716-446655440002';

        $client->request(
            'GET',
            sprintf(
                '/api/contents/%s/artifacts/%s/recommendations',
                $contentId->value,
                $artifactId,
            ),
        );

        self::assertResponseStatusCodeSame(200);
        self::assertJsonStringEqualsJsonString(
            '{"recommendations":[]}',
            $client->getResponse()->getContent(),
        );
    }

    public function testUnknownCurrentArtifactReturnsEmptyRecommendations(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $contentId = ContentId::generate();
        $summaryId = new ArtifactId('550e8400-e29b-41d4-a716-446655440002');
        $unknownId = '550e8400-e29b-41d4-a716-446655440099';

        $repository = static::getContainer()->get(ArtifactRepositoryInterface::class);
        $repository->save(Artifact::create(
            $summaryId,
            $contentId,
            ProcessingJobId::generate(),
            ArtifactType::Summary,
            ArtifactContent::fromString('Summary text'),
        ));

        $client->request(
            'GET',
            sprintf(
                '/api/contents/%s/artifacts/%s/recommendations',
                $contentId->value,
                $unknownId,
            ),
        );

        self::assertResponseStatusCodeSame(200);
        self::assertJsonStringEqualsJsonString(
            '{"recommendations":[]}',
            $client->getResponse()->getContent(),
        );
    }

    public function testInvalidContentIdReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/api/contents/not-a-valid-uuid/artifacts/550e8400-e29b-41d4-a716-446655440002/recommendations',
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
                '/api/contents/%s/artifacts/not-a-valid-uuid/recommendations',
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
     * @param list<array{artifactId: string, type: string, title: string, reason: string, score: int}> $recommendations
     */
    private function containsRecommendation(
        array $recommendations,
        string $artifactId,
        string $type,
        string $title,
        string $reason,
        int $score,
    ): bool {
        foreach ($recommendations as $recommendation) {
            if (
                $recommendation['artifactId'] === $artifactId
                && $recommendation['type'] === $type
                && $recommendation['title'] === $title
                && $recommendation['reason'] === $reason
                && $recommendation['score'] === $score
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<array{artifactId: string, type: string, title: string, reason: string, score: int}> $recommendations
     *
     * @return list<string>
     */
    private function uniqueRecommendations(array $recommendations): array
    {
        return array_values(array_unique(array_map(
            static fn (array $recommendation): string => $recommendation['artifactId'],
            $recommendations,
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
