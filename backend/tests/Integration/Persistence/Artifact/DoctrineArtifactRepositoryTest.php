<?php

declare(strict_types=1);

namespace App\Tests\Integration\Persistence\Artifact;

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
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineArtifactRepositoryTest extends KernelTestCase
{
    private ArtifactRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->resetDatabaseSchema();
        $this->repository = static::getContainer()->get(ArtifactRepositoryInterface::class);
    }

    public function testSaveAndFindArtifact(): void
    {
        $artifactId = ArtifactId::generate();
        $contentId = ContentId::generate();
        $processingJobId = ProcessingJobId::generate();
        $artifact = Artifact::create(
            $artifactId,
            $contentId,
            $processingJobId,
            ArtifactType::Summary,
            ArtifactContent::fromString('Summary text produced by processing.'),
        );

        $this->repository->save($artifact);

        $found = $this->repository->findById($artifactId);

        self::assertNotNull($found);
        self::assertTrue($found->id()->equals($artifactId));
        self::assertTrue($found->contentId()->equals($contentId));
        self::assertTrue($found->processingJobId()->equals($processingJobId));
        self::assertSame(ArtifactType::Summary, $found->type());
        self::assertSame('Summary text produced by processing.', $found->content()->value());
        self::assertSame(
            $artifact->createdAt()->format('Y-m-d H:i:s'),
            $found->createdAt()->format('Y-m-d H:i:s'),
        );
    }

    public function testFindByIdReturnsNullWhenMissing(): void
    {
        self::assertNull($this->repository->findById(ArtifactId::generate()));
    }

    #[DataProvider('artifactTypeProvider')]
    public function testPersistsAllArtifactTypes(ArtifactType $type, string $content): void
    {
        $artifact = Artifact::create(
            ArtifactId::generate(),
            ContentId::generate(),
            ProcessingJobId::generate(),
            $type,
            ArtifactContent::fromString($content),
        );

        $this->repository->save($artifact);

        $found = $this->repository->findById($artifact->id());

        self::assertNotNull($found);
        self::assertSame($type, $found->type());
        self::assertSame($content, $found->content()->value());
    }

    /**
     * @return iterable<string, array{ArtifactType, string}>
     */
    public static function artifactTypeProvider(): iterable
    {
        yield 'summary' => [ArtifactType::Summary, 'Summary output'];
        yield 'quiz' => [ArtifactType::Quiz, '{"questions":[]}'];
        yield 'flashcards' => [ArtifactType::Flashcards, '{"cards":[]}'];
        yield 'podcast' => [ArtifactType::Podcast, 'Podcast script'];
        yield 'timeline' => [ArtifactType::Timeline, '{"events":[]}'];
        yield 'transcript' => [ArtifactType::Transcript, 'Full transcript text'];
    }

    public function testMultipleArtifactsCanBePersistedForSameContent(): void
    {
        $contentId = ContentId::generate();
        $summary = Artifact::create(
            ArtifactId::generate(),
            $contentId,
            ProcessingJobId::generate(),
            ArtifactType::Summary,
            ArtifactContent::fromString('Summary artifact'),
        );
        $quiz = Artifact::create(
            ArtifactId::generate(),
            $contentId,
            ProcessingJobId::generate(),
            ArtifactType::Quiz,
            ArtifactContent::fromString('Quiz artifact'),
        );

        $this->repository->save($summary);
        $this->repository->save($quiz);

        $foundSummary = $this->repository->findById($summary->id());
        $foundQuiz = $this->repository->findById($quiz->id());

        self::assertNotNull($foundSummary);
        self::assertNotNull($foundQuiz);
        self::assertTrue($foundSummary->contentId()->equals($contentId));
        self::assertTrue($foundQuiz->contentId()->equals($contentId));
        self::assertSame(ArtifactType::Summary, $foundSummary->type());
        self::assertSame(ArtifactType::Quiz, $foundQuiz->type());
    }

    public function testSaveIsIdempotentForExistingArtifact(): void
    {
        $artifact = Artifact::create(
            ArtifactId::generate(),
            ContentId::generate(),
            ProcessingJobId::generate(),
            ArtifactType::Transcript,
            ArtifactContent::fromString('Transcript body'),
        );

        $this->repository->save($artifact);
        $this->repository->save($artifact);

        $found = $this->repository->findById($artifact->id());

        self::assertNotNull($found);
        self::assertSame('Transcript body', $found->content()->value());
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
