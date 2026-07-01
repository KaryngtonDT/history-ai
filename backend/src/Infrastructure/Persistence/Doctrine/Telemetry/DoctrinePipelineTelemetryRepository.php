<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Telemetry;

use App\Domain\Telemetry\PipelineTelemetry;
use App\Domain\Telemetry\PipelineTelemetryRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrinePipelineTelemetryRepository implements PipelineTelemetryRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TelemetryJsonMapper $mapper,
    ) {
    }

    public function append(PipelineTelemetry $telemetry): void
    {
        $this->entityManager->persist(
            PipelineTelemetryRecord::fromPayload($this->mapper->toPayload($telemetry)),
        );
        $this->entityManager->flush();
    }

    public function findByWorkspaceId(string $workspaceId): array
    {
        /** @var list<PipelineTelemetryRecord> $records */
        $records = $this->entityManager->getRepository(PipelineTelemetryRecord::class)->findBy(
            ['workspaceId' => $workspaceId],
            ['recordedAt' => 'DESC'],
        );

        return array_map(
            fn (PipelineTelemetryRecord $record): PipelineTelemetry => $this->mapper->fromPayload($record->payload()),
            $records,
        );
    }
}
