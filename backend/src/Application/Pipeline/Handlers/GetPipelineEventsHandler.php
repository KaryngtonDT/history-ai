<?php

declare(strict_types=1);

namespace App\Application\Pipeline\Handlers;

use App\Application\Pipeline\Orchestration\PipelineOrchestrator;
use App\Domain\PipelineJob\PipelineNotificationRepositoryInterface;

final class GetPipelineEventsHandler
{
    public function __construct(
        private readonly PipelineNotificationRepositoryInterface $notificationRepository,
        private readonly PipelineOrchestrator $orchestrator,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $sourceId): array
    {
        $notifications = $this->notificationRepository->findBySourceId($sourceId);

        return [
            'sourceId' => $sourceId,
            'events' => array_map(static fn ($n): array => [
                'notificationId' => $n->notificationId()->value,
                'sourceId' => $n->sourceId(),
                'stage' => $n->stage()?->value,
                'type' => $n->type()->value,
                'title' => $n->title(),
                'message' => $n->message(),
                'read' => $n->read(),
                'createdAt' => $n->createdAt()->format(DATE_ATOM),
                'actionUrl' => $n->actionUrl(),
            ], $notifications),
            'status' => $this->orchestrator->buildSourceStatus($sourceId),
        ];
    }
}
