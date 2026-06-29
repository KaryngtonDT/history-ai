<?php

declare(strict_types=1);

namespace App\Tests\Integration\Persistence\Video;

use App\Domain\Video\VideoId;
use App\Domain\Video\VideoJob;
use App\Domain\Video\VideoLanguage;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\Video\VideoStatus;
use App\Infrastructure\Persistence\Doctrine\Video\VideoJobRecord;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineVideoRepositoryTest extends KernelTestCase
{
    private VideoRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->resetDatabaseSchema();
        $this->repository = static::getContainer()->get(VideoRepositoryInterface::class);
    }

    public function testSaveAndFindQueuedVideoJob(): void
    {
        $videoId = VideoId::generate();
        $job = VideoJob::reconstitute(
            $videoId,
            'lecture.mp4',
            VideoLanguage::English,
            VideoStatus::Queued,
            new DateTimeImmutable('2026-06-26T12:00:00+00:00'),
            '/var/video-storage/lecture.mp4',
        );

        $this->repository->save($job);

        $found = $this->repository->findById($videoId);

        self::assertNotNull($found);
        self::assertTrue($found->id()->equals($videoId));
        self::assertSame('lecture.mp4', $found->originalFilename());
        self::assertSame(VideoLanguage::English, $found->language());
        self::assertSame(VideoStatus::Queued, $found->status());
        self::assertSame('/var/video-storage/lecture.mp4', $found->storagePath());
    }

    private function resetDatabaseSchema(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $metadata = $entityManager->getMetadataFactory()->getMetadataFor(VideoJobRecord::class);
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema([$metadata]);
        $schemaTool->createSchema([$metadata]);
    }
}
