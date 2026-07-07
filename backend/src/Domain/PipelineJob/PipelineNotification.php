<?php

declare(strict_types=1);

namespace App\Domain\PipelineJob;

use App\Domain\Pipeline\PipelineStageType;
use DateTimeImmutable;

final class PipelineNotification
{
    private function __construct(
        private readonly PipelineNotificationId $notificationId,
        private readonly string $sourceId,
        private readonly ?PipelineStageType $stage,
        private readonly PipelineNotificationType $type,
        private readonly string $title,
        private readonly string $message,
        private bool $read,
        private readonly ?string $actionUrl,
        private readonly DateTimeImmutable $createdAt,
    ) {
    }

    public static function create(
        PipelineNotificationId $notificationId,
        string $sourceId,
        PipelineNotificationType $type,
        string $title,
        string $message,
        ?PipelineStageType $stage = null,
        ?string $actionUrl = null,
    ): self {
        return new self(
            $notificationId,
            $sourceId,
            $stage,
            $type,
            $title,
            $message,
            false,
            $actionUrl,
            new DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        PipelineNotificationId $notificationId,
        string $sourceId,
        ?PipelineStageType $stage,
        PipelineNotificationType $type,
        string $title,
        string $message,
        bool $read,
        ?string $actionUrl,
        DateTimeImmutable $createdAt,
    ): self {
        return new self(
            $notificationId,
            $sourceId,
            $stage,
            $type,
            $title,
            $message,
            $read,
            $actionUrl,
            $createdAt,
        );
    }

    public function markRead(): self
    {
        return new self(
            $this->notificationId,
            $this->sourceId,
            $this->stage,
            $this->type,
            $this->title,
            $this->message,
            true,
            $this->actionUrl,
            $this->createdAt,
        );
    }

    public function notificationId(): PipelineNotificationId
    {
        return $this->notificationId;
    }

    public function sourceId(): string
    {
        return $this->sourceId;
    }

    public function stage(): ?PipelineStageType
    {
        return $this->stage;
    }

    public function type(): PipelineNotificationType
    {
        return $this->type;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function read(): bool
    {
        return $this->read;
    }

    public function actionUrl(): ?string
    {
        return $this->actionUrl;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
