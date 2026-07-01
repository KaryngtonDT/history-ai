<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Workspace;

use App\Domain\Video\VideoId;
use App\Domain\Workspace\Project;
use App\Domain\Workspace\ProjectId;
use App\Domain\Workspace\ProjectRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineProjectRepository implements ProjectRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function save(Project $project): void
    {
        $repository = $this->entityManager->getRepository(ProjectRecord::class);
        $record = $repository->find($project->id()->value);

        if (null === $record) {
            $this->entityManager->persist(ProjectRecord::fromDomain($project));
        } else {
            $record->updateFromDomain($project);
        }

        $this->entityManager->flush();
    }

    public function findById(ProjectId $id): ?Project
    {
        $record = $this->entityManager->find(ProjectRecord::class, $id->value);

        return $record?->toDomain();
    }

    public function findProjectIdByVideoId(VideoId $videoId): ?ProjectId
    {
        foreach ($this->findAll() as $project) {
            foreach ($project->videos()->all() as $projectVideo) {
                if ($projectVideo->videoId()->equals($videoId)) {
                    return $project->id();
                }
            }
        }

        return null;
    }

    public function findAll(): array
    {
        /** @var list<ProjectRecord> $records */
        $records = $this->entityManager->getRepository(ProjectRecord::class)->findBy(
            [],
            ['createdAt' => 'DESC'],
        );

        return array_map(
            static fn (ProjectRecord $record): Project => $record->toDomain(),
            $records,
        );
    }

    public function delete(ProjectId $id): void
    {
        $record = $this->entityManager->find(ProjectRecord::class, $id->value);

        if (null === $record) {
            return;
        }

        $this->entityManager->remove($record);
        $this->entityManager->flush();
    }
}
