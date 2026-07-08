<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\EngineAnalytics;

use App\Domain\EngineAnalytics\EngineExecutionHistory;
use App\Domain\EngineAnalytics\EngineExecutionHistoryId;
use App\Domain\EngineAnalytics\EngineExecutionHistoryRepositoryInterface;
use App\Domain\EngineAnalytics\EngineExecutionStatus;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineJobId;
use DateTimeImmutable;

final class InMemoryEngineExecutionHistoryRepository implements EngineExecutionHistoryRepositoryInterface
{
    /** @var array<string, EngineExecutionHistory> */
    private array $items = [];

    public function record(EngineExecutionHistory $execution): void
    {
        $this->items[$execution->executionId()->value] = $execution;
    }

    public function findById(EngineExecutionHistoryId $executionId): ?EngineExecutionHistory
    {
        return $this->items[$executionId->value] ?? null;
    }

    public function findLatestByPipelineJobId(PipelineJobId $pipelineJobId): ?EngineExecutionHistory
    {
        $matches = array_values(array_filter(
            $this->items,
            static fn (EngineExecutionHistory $item): bool => $item->pipelineJobId()->value === $pipelineJobId->value,
        ));
        usort(
            $matches,
            static fn (EngineExecutionHistory $left, EngineExecutionHistory $right): int => $right->completedAt() <=> $left->completedAt(),
        );

        return $matches[0] ?? null;
    }

    public function findRecent(
        ?PipelineStageType $stage = null,
        ?string $engineId = null,
        ?string $hardwareProfile = null,
        int $limit = 20,
    ): array {
        $matches = array_values(array_filter(
            $this->items,
            static function (EngineExecutionHistory $item) use ($stage, $engineId, $hardwareProfile): bool {
                if (null !== $stage && $item->stage() !== $stage) {
                    return false;
                }

                if (null !== $engineId && $item->engineId() !== $engineId) {
                    return false;
                }

                if (null !== $hardwareProfile && $item->hardwareProfile() !== $hardwareProfile) {
                    return false;
                }

                return true;
            },
        ));
        usort(
            $matches,
            static fn (EngineExecutionHistory $left, EngineExecutionHistory $right): int => $right->completedAt() <=> $left->completedAt(),
        );

        return array_slice($matches, 0, $limit);
    }

    public function findByEngineId(string $engineId, int $limit = 50): array
    {
        return $this->findRecent(engineId: $engineId, limit: $limit);
    }
}
