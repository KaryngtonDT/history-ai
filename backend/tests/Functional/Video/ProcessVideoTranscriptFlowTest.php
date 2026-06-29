<?php

declare(strict_types=1);

namespace App\Tests\Functional\Video;

use App\Application\Video\Handlers\ProcessVideoHandler;
use App\Application\Video\Messages\ProcessVideoMessage;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\Speech\TranscriptRepositoryInterface;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoJob;
use App\Domain\Video\VideoLanguage;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\Video\VideoStatus;
use App\Infrastructure\Persistence\Doctrine\Artifact\ArtifactRecord;
use App\Infrastructure\Persistence\Doctrine\Speech\TranscriptRecord;
use App\Infrastructure\Persistence\Doctrine\Video\VideoJobRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProcessVideoTranscriptFlowTest extends WebTestCase
{
    public function testProcessVideoCreatesTranscriptAndArtifact(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $videoId = VideoId::generate();
        $job = VideoJob::createUploaded($videoId, 'lecture.mp4', VideoLanguage::Unknown)
            ->withStoragePath('/tmp/lecture.mp4')
            ->queue();

        $videoRepository = static::getContainer()->get(VideoRepositoryInterface::class);
        $videoRepository->save($job);

        $handler = static::getContainer()->get(ProcessVideoHandler::class);
        ($handler)(new ProcessVideoMessage($videoId->value));

        $updatedJob = $videoRepository->findById($videoId);
        self::assertNotNull($updatedJob);
        self::assertSame(VideoStatus::Completed, $updatedJob->status());

        $transcriptRepository = static::getContainer()->get(TranscriptRepositoryInterface::class);
        $transcript = $transcriptRepository->findByVideoId($videoId);
        self::assertNotNull($transcript);
        self::assertSame(2, $transcript->segmentCount());

        $artifactRepository = static::getContainer()->get(ArtifactRepositoryInterface::class);
        $artifacts = $artifactRepository->findByContentId(new ContentId($videoId->value));
        self::assertCount(1, $artifacts);
        self::assertSame(ArtifactType::Transcript, $artifacts[0]->type());

        $client->request('GET', '/api/videos/'.$videoId->value.'/transcript');
        self::assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame($videoId->value, $response['videoId']);
        self::assertSame(2, $response['segmentCount']);
    }

    private function resetDatabaseSchema(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $metadata = [
            $entityManager->getClassMetadata(VideoJobRecord::class),
            $entityManager->getClassMetadata(TranscriptRecord::class),
            $entityManager->getClassMetadata(ArtifactRecord::class),
        ];
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }
}
