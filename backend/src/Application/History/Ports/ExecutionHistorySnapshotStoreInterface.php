<?php

declare(strict_types=1);

namespace App\Application\History\Ports;

use App\Application\History\ExecutionVersionSnapshot;
use App\Domain\History\ExecutionHistory;
use App\Domain\Video\VideoId;

interface ExecutionHistorySnapshotStoreInterface
{
    /**
     * @return list<ExecutionVersionSnapshot>
     */
    public function findAllByVideoId(VideoId $videoId): array;

    public function findByVideoIdAndVersion(VideoId $videoId, int $versionNumber): ?ExecutionVersionSnapshot;

    public function append(ExecutionHistory $history, ExecutionVersionSnapshot $snapshot): void;
}
