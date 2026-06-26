<?php

declare(strict_types=1);

namespace App\Tests\Integration\Persistence\Processing;

use App\Domain\Content\ContentId;
use App\Domain\Processing\ProcessingJob;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Processing\ProcessingJobProgress;
use App\Domain\Processing\ProcessingJobRepositoryInterface;
use App\Domain\Processing\ProcessingJobStatus;
use App\Domain\Processing\ProcessingJobType;
use App\Infrastructure\Persistence\Doctrine\Processing\ProcessingJobRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineProcessingJobRepositoryTest extends KernelTestCase
{
    private ProcessingJobRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->resetDatabaseSchema();
        $this->repository = static::getContainer()->get(ProcessingJobRepositoryInterface::class);
    }

    public function testSaveAndFindPendingJob(): void
    {
        $contentId = ContentId::generate();
        $jobId = ProcessingJobId::generate();
        $job = ProcessingJob::create($jobId, $contentId, ProcessingJobType::Summary);

        $this->repository->save($job);

        $found = $this->repository->findById($jobId);

        self::assertNotNull($found);
        self::assertTrue($found->id()->equals($jobId));
        self::assertTrue($found->contentId()->equals($contentId));
        self::assertSame(ProcessingJobType::Summary, $found->type());
        self::assertSame(ProcessingJobStatus::Pending, $found->status());
        self::assertSame(0, $found->progress()->percentage());
        self::assertNull($found->startedAt());
        self::assertNull($found->completedAt());
        self::assertNull($found->failedAt());
    }

    public function testFindByIdReturnsNullWhenMissing(): void
    {
        self::assertNull($this->repository->findById(ProcessingJobId::generate()));
    }

    public function testSaveUpdatesExistingJobAfterLifecycleChanges(): void
    {
        $job = ProcessingJob::create(
            ProcessingJobId::generate(),
            ContentId::generate(),
            ProcessingJobType::Quiz,
        );

        $this->repository->save($job);

        $job->start();
        $job->updateProgress(ProcessingJobProgress::fromPercentage(42));
        $this->repository->save($job);

        $found = $this->repository->findById($job->id());

        self::assertNotNull($found);
        self::assertSame(ProcessingJobStatus::Running, $found->status());
        self::assertSame(42, $found->progress()->percentage());
        self::assertNotNull($found->startedAt());
    }

    public function testPersistsCompletedJobWithTimestamps(): void
    {
        $job = ProcessingJob::create(
            ProcessingJobId::generate(),
            ContentId::generate(),
            ProcessingJobType::Podcast,
        );
        $job->start();
        $job->updateProgress(ProcessingJobProgress::fromPercentage(75));
        $job->complete();

        $this->repository->save($job);

        $found = $this->repository->findById($job->id());

        self::assertNotNull($found);
        self::assertSame(ProcessingJobStatus::Completed, $found->status());
        self::assertSame(100, $found->progress()->percentage());
        self::assertNotNull($found->startedAt());
        self::assertNotNull($found->completedAt());
        self::assertNull($found->failedAt());
    }

    public function testPersistsFailedJob(): void
    {
        $job = ProcessingJob::create(
            ProcessingJobId::generate(),
            ContentId::generate(),
            ProcessingJobType::Flashcards,
        );
        $job->start();
        $job->updateProgress(ProcessingJobProgress::fromPercentage(30));
        $job->fail();

        $this->repository->save($job);

        $found = $this->repository->findById($job->id());

        self::assertNotNull($found);
        self::assertSame(ProcessingJobStatus::Failed, $found->status());
        self::assertSame(30, $found->progress()->percentage());
        self::assertNotNull($found->failedAt());
    }

    public function testMultipleJobsCanBePersistedForSameContent(): void
    {
        $contentId = ContentId::generate();
        $summary = ProcessingJob::create(
            ProcessingJobId::generate(),
            $contentId,
            ProcessingJobType::Summary,
        );
        $quiz = ProcessingJob::create(
            ProcessingJobId::generate(),
            $contentId,
            ProcessingJobType::Quiz,
        );

        $this->repository->save($summary);
        $this->repository->save($quiz);

        $foundSummary = $this->repository->findById($summary->id());
        $foundQuiz = $this->repository->findById($quiz->id());

        self::assertNotNull($foundSummary);
        self::assertNotNull($foundQuiz);
        self::assertTrue($foundSummary->contentId()->equals($contentId));
        self::assertTrue($foundQuiz->contentId()->equals($contentId));
        self::assertSame(ProcessingJobType::Summary, $foundSummary->type());
        self::assertSame(ProcessingJobType::Quiz, $foundQuiz->type());
    }

    private function resetDatabaseSchema(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $metadata = $entityManager->getMetadataFactory()->getMetadataFor(ProcessingJobRecord::class);
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema([$metadata]);
        $schemaTool->createSchema([$metadata]);
    }
}
