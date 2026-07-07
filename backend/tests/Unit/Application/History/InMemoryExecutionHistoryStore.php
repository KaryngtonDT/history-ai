<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\History;

use App\Application\History\ExecutionVersionSnapshot;
use App\Application\History\Ports\ExecutionHistorySnapshotStoreInterface;
use App\Domain\History\ExecutionHistory;
use App\Domain\Video\VideoId;

final class InMemoryExecutionHistoryStore implements ExecutionHistorySnapshotStoreInterface
{
    /** @var array<string, list<ExecutionVersionSnapshot>> */
    private array $snapshots = [];

    /** @var array<string, ExecutionHistory> */
    private array $histories = [];

    public function findAllByVideoId(VideoId $videoId): array
    {
        return $this->snapshots[$videoId->value] ?? [];
    }

    public function findByVideoIdAndVersion(VideoId $videoId, int $versionNumber): ?ExecutionVersionSnapshot
    {
        foreach ($this->findAllByVideoId($videoId) as $snapshot) {
            if ($snapshot->version->versionNumber() === $versionNumber) {
                return $snapshot;
            }
        }

        return null;
    }

    public function append(ExecutionHistory $history, ExecutionVersionSnapshot $snapshot): void
    {
        $this->histories[$history->videoId()->value] = $history;
        $this->snapshots[$history->videoId()->value] ??= [];
        $this->snapshots[$history->videoId()->value][] = $snapshot;
    }

    public function rememberHistory(ExecutionHistory $history): void
    {
        $this->histories[$history->videoId()->value] = $history;
    }

    public function findHistory(VideoId $videoId): ?ExecutionHistory
    {
        return $this->histories[$videoId->value] ?? null;
    }
}
