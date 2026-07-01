<?php

declare(strict_types=1);

namespace App\Tests\Functional\YouTube;

use App\Application\Video\Handlers\ProcessVideoHandler;
use App\Application\Video\Messages\ProcessVideoMessage;
use App\Application\YouTube\Commands\ImportYouTubeCommand;
use App\Application\YouTube\Handlers\ImportYouTubeHandler;
use App\Domain\Speech\TranscriptRepositoryInterface;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\Video\VideoStatus;
use App\Infrastructure\Persistence\Doctrine\Artifact\ArtifactRecord;
use App\Infrastructure\Persistence\Doctrine\Pipeline\PipelineConfigurationRecord;
use App\Infrastructure\Persistence\Doctrine\Source\SourceRecord;
use App\Infrastructure\Persistence\Doctrine\Speech\TranscriptRecord;
use App\Infrastructure\Persistence\Doctrine\Translation\TranslationRecord;
use App\Infrastructure\Persistence\Doctrine\Video\VideoJobRecord;
use App\Infrastructure\Persistence\Doctrine\VideoRender\FinalVideoRecord;
use App\Infrastructure\Persistence\Doctrine\YouTube\YouTubeImportRecord;
use App\Infrastructure\Video\MessengerVideoProcessingQueue;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class YouTubeVideoPipelineFlowTest extends WebTestCase
{
    public function testImportedYouTubeVideoRunsStandardPipeline(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $container = static::getContainer();
        $container->set(
            'App\Application\Video\Ports\VideoProcessingQueueInterface',
            $container->get(MessengerVideoProcessingQueue::class),
        );

        $importHandler = $container->get(ImportYouTubeHandler::class);
        $result = $importHandler(new ImportYouTubeCommand(
            url: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ));

        $processHandler = $container->get(ProcessVideoHandler::class);
        ($processHandler)(new ProcessVideoMessage($result->videoId->value));

        $videoRepository = $container->get(VideoRepositoryInterface::class);
        $job = $videoRepository->findById(new VideoId($result->videoId->value));

        self::assertNotNull($job);
        self::assertSame(VideoStatus::Completed, $job->status());

        $transcriptRepository = $container->get(TranscriptRepositoryInterface::class);
        self::assertNotNull($transcriptRepository->findByVideoId($result->videoId));
    }

    private function resetDatabaseSchema(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $metadata = [
            $entityManager->getClassMetadata(YouTubeImportRecord::class),
            $entityManager->getClassMetadata(SourceRecord::class),
            $entityManager->getClassMetadata(VideoJobRecord::class),
            $entityManager->getClassMetadata(TranscriptRecord::class),
            $entityManager->getClassMetadata(TranslationRecord::class),
            $entityManager->getClassMetadata(ArtifactRecord::class),
            $entityManager->getClassMetadata(PipelineConfigurationRecord::class),
            $entityManager->getClassMetadata(FinalVideoRecord::class),
        ];
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }
}
