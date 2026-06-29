<?php

declare(strict_types=1);

namespace App\Tests\Functional\Graph;

use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Chat\Conversation;
use App\Domain\Chat\ConversationId;
use App\Domain\Chat\ConversationRepositoryInterface;
use App\Domain\Chat\SelectedDocument;
use App\Domain\Chat\SelectedDocumentCollection;
use App\Domain\Content\ContentId;
use App\Domain\Processing\ProcessingJobId;
use App\Infrastructure\Persistence\Doctrine\Artifact\ArtifactRecord;
use App\Infrastructure\Persistence\Doctrine\Chat\ConversationRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class GetConversationKnowledgeGraphControllerTest extends WebTestCase
{
    private const string CONVERSATION_ID = '550e8400-e29b-41d4-a716-446655440001';
    private const string FIRST_CONTENT_ID = '550e8400-e29b-41d4-a716-446655440010';
    private const string SECOND_CONTENT_ID = '550e8400-e29b-41d4-a716-446655440020';

    public function testValidRequestReturnsConversationScopedGraphJson(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $firstTranscriptId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $firstSummaryId = new ArtifactId('550e8400-e29b-41d4-a716-446655440002');
        $secondQuizId = new ArtifactId('550e8400-e29b-41d4-a716-446655440003');
        $excludedTimelineId = new ArtifactId('550e8400-e29b-41d4-a716-446655440004');

        $artifactRepository = static::getContainer()->get(ArtifactRepositoryInterface::class);
        $artifactRepository->save(Artifact::create(
            $firstTranscriptId,
            new ContentId(self::FIRST_CONTENT_ID),
            ProcessingJobId::generate(),
            ArtifactType::Transcript,
            ArtifactContent::fromString('Transcript text'),
        ));
        $artifactRepository->save(Artifact::create(
            $firstSummaryId,
            new ContentId(self::FIRST_CONTENT_ID),
            ProcessingJobId::generate(),
            ArtifactType::Summary,
            ArtifactContent::fromString('Summary text'),
        ));
        $artifactRepository->save(Artifact::create(
            $secondQuizId,
            new ContentId(self::SECOND_CONTENT_ID),
            ProcessingJobId::generate(),
            ArtifactType::Quiz,
            ArtifactContent::fromString('Quiz text'),
        ));
        $artifactRepository->save(Artifact::create(
            $excludedTimelineId,
            ContentId::generate(),
            ProcessingJobId::generate(),
            ArtifactType::Timeline,
            ArtifactContent::fromString('Timeline text'),
        ));

        $conversationRepository = static::getContainer()->get(ConversationRepositoryInterface::class);
        $conversationRepository->save(new Conversation(
            new ConversationId(self::CONVERSATION_ID),
            new SelectedDocumentCollection([
                new SelectedDocument(new ContentId(self::FIRST_CONTENT_ID)),
                new SelectedDocument(new ContentId(self::SECOND_CONTENT_ID)),
            ]),
        ));

        $client->request('GET', sprintf('/api/conversations/%s/graph', self::CONVERSATION_ID));

        self::assertResponseStatusCodeSame(200);

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('nodes', $response);
        self::assertArrayHasKey('edges', $response);
        self::assertSame(3, count($response['nodes']));
        self::assertSame(
            [
                $firstTranscriptId->value,
                $firstSummaryId->value,
                $secondQuizId->value,
            ],
            array_column($response['nodes'], 'artifactId'),
        );
        self::assertFalse($this->containsNode($response['nodes'], $excludedTimelineId->value));
        self::assertTrue($this->containsEdge(
            $response['edges'],
            $firstSummaryId->value,
            $firstTranscriptId->value,
            'derived_from',
        ));
    }

    public function testUnknownConversationReturnsNotFound(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request('GET', sprintf('/api/conversations/%s/graph', self::CONVERSATION_ID));

        self::assertResponseStatusCodeSame(404);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Conversation not found"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testInvalidConversationIdReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/conversations/not-a-valid-uuid/graph');

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    /**
     * @param list<array{artifactId: string, type: string, title: string}> $nodes
     */
    private function containsNode(array $nodes, string $artifactId): bool
    {
        foreach ($nodes as $node) {
            if ($node['artifactId'] === $artifactId) {
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

    private function resetDatabaseSchema(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $artifactMetadata = $entityManager->getMetadataFactory()->getMetadataFor(ArtifactRecord::class);
        $conversationMetadata = $entityManager->getMetadataFactory()->getMetadataFor(ConversationRecord::class);
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema([$artifactMetadata, $conversationMetadata]);
        $schemaTool->createSchema([$artifactMetadata, $conversationMetadata]);
    }
}
