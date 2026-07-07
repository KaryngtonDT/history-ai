<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\History;

use App\Domain\History\ExecutionHistory;
use App\Domain\History\ExecutionHistoryId;
use App\Domain\History\ExecutionHistoryRepositoryInterface;
use App\Domain\Video\VideoId;

final class InMemoryExecutionHistoryRepository implements ExecutionHistoryRepositoryInterface
{
    public function __construct(
        private readonly InMemoryExecutionHistoryStore $store,
    ) {
    }

    public function save(ExecutionHistory $history): void
    {
        $this->store->rememberHistory($history);
    }

    public function findByVideoId(VideoId $videoId): ?ExecutionHistory
    {
        return $this->store->findHistory($videoId);
    }

    public function findOrCreateForVideo(VideoId $videoId): ExecutionHistory
    {
        return $this->store->findHistory($videoId)
            ?? ExecutionHistory::create(ExecutionHistoryId::generate(), $videoId);
    }
}
