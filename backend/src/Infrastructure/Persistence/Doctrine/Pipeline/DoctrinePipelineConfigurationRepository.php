<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Pipeline;

use App\Application\Pipeline\PipelineConfigurationJsonMapper;
use App\Domain\Pipeline\PipelineConfiguration;
use App\Domain\Pipeline\PipelineConfigurationRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrinePipelineConfigurationRepository implements PipelineConfigurationRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PipelineConfigurationJsonMapper $jsonMapper,
    ) {
    }

    public function save(PipelineConfiguration $configuration): void
    {
        $now = new \DateTimeImmutable();
        $createdAt = $configuration->createdAt() ?? $now;
        $updatedAt = $configuration->updatedAt() ?? $now;

        $persisted = $configuration->withTimestamps($createdAt, $updatedAt);

        $this->entityManager->persist(new PipelineConfigurationRecord(
            $persisted->id()->value,
            $persisted->version(),
            $this->jsonMapper->toJson($persisted),
            $createdAt,
            $updatedAt,
        ));

        $this->entityManager->flush();
    }

    public function findLatest(): ?PipelineConfiguration
    {
        /** @var PipelineConfigurationRecord|null $record */
        $record = $this->entityManager->getRepository(PipelineConfigurationRecord::class)
            ->findOneBy([], ['updatedAt' => 'DESC']);

        if (null === $record) {
            return null;
        }

        return $this->jsonMapper->fromJson($record->payload());
    }

    public function deleteAll(): void
    {
        $this->entityManager->createQueryBuilder()
            ->delete(PipelineConfigurationRecord::class, 'pipeline_configuration')
            ->getQuery()
            ->execute();
    }
}
