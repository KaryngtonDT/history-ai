<?php

declare(strict_types=1);

namespace App\Tests\Functional\Audio;

use App\Application\AudioUpload\Handlers\ProcessAudioHandler;
use App\Application\AudioUpload\Messages\ProcessAudioMessage;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\Source\Source;
use App\Domain\Source\SourceId;
use App\Domain\Source\SourceMetadata;
use App\Domain\Source\SourceRepositoryInterface;
use App\Domain\Source\SourceStatus;
use App\Domain\Source\SourceType;
use App\Domain\Speech\TranscriptRepositoryInterface;
use App\Domain\Translation\TranslationRepositoryInterface;
use App\Domain\Video\VideoId;
use App\Infrastructure\Persistence\Doctrine\Artifact\ArtifactRecord;
use App\Infrastructure\Persistence\Doctrine\Pipeline\PipelineConfigurationRecord;
use App\Infrastructure\Persistence\Doctrine\Source\SourceRecord;
use App\Infrastructure\Persistence\Doctrine\Speech\TranscriptRecord;
use App\Infrastructure\Persistence\Doctrine\Translation\TranslationRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProcessAudioTranscriptFlowTest extends WebTestCase
{
    public function testProcessAudioCreatesTranscriptAndTranslations(): void
    {
        static::createClient();
        $this->resetDatabaseSchema();

        $audioId = SourceId::generate();
        $source = Source::createUploaded(
            $audioId,
            SourceType::Audio,
            new SourceMetadata('podcast.mp3'),
        )
            ->withStoragePath('/tmp/podcast.mp3')
            ->queue();

        $sourceRepository = static::getContainer()->get(SourceRepositoryInterface::class);
        $sourceRepository->save($source);

        $handler = static::getContainer()->get(ProcessAudioHandler::class);
        ($handler)(new ProcessAudioMessage($audioId->value));

        $updated = $sourceRepository->findById($audioId);
        self::assertNotNull($updated);
        self::assertSame(SourceStatus::Completed, $updated->status());

        $transcriptRepository = static::getContainer()->get(TranscriptRepositoryInterface::class);
        $transcript = $transcriptRepository->findByVideoId(new VideoId($audioId->value));
        self::assertNotNull($transcript);

        $artifactRepository = static::getContainer()->get(ArtifactRepositoryInterface::class);
        $artifacts = $artifactRepository->findByContentId(new ContentId($audioId->value));
        self::assertNotEmpty($artifacts);
        self::assertContains(
            ArtifactType::Transcript,
            array_map(static fn ($artifact) => $artifact->type(), $artifacts),
        );

        $translationRepository = static::getContainer()->get(TranslationRepositoryInterface::class);
        $translations = $translationRepository->findAllByVideoId(new VideoId($audioId->value));
        self::assertCount(2, $translations);
    }

    private function resetDatabaseSchema(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $metadata = [
            $entityManager->getClassMetadata(SourceRecord::class),
            $entityManager->getClassMetadata(TranscriptRecord::class),
            $entityManager->getClassMetadata(TranslationRecord::class),
            $entityManager->getClassMetadata(ArtifactRecord::class),
            $entityManager->getClassMetadata(PipelineConfigurationRecord::class),
        ];
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }
}
