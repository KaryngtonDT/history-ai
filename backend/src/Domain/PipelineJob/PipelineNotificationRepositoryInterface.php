<?php

declare(strict_types=1);

namespace App\Domain\PipelineJob;

interface PipelineNotificationRepositoryInterface
{
    public function save(PipelineNotification $notification): void;

    /** @return list<PipelineNotification> */
    public function findBySourceId(string $sourceId, int $limit = 50): array;

    /** @return list<PipelineNotification> */
    public function findUnreadBySourceId(string $sourceId): array;
}
