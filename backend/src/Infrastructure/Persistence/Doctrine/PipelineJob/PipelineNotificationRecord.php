<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\PipelineJob;

use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineNotification;
use App\Domain\PipelineJob\PipelineNotificationId;
use App\Domain\PipelineJob\PipelineNotificationType;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'pipeline_notification')]
class PipelineNotificationRecord
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID)]
    private string $id;

    #[ORM\Column(name: 'source_id', type: Types::GUID)]
    private string $sourceId;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $stage = null;

    #[ORM\Column(length: 64, enumType: PipelineNotificationType::class)]
    private PipelineNotificationType $type;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: Types::TEXT)]
    private string $message;

    #[ORM\Column(name: 'read_flag', type: Types::BOOLEAN)]
    private bool $readFlag = false;

    #[ORM\Column(name: 'action_url', length: 512, nullable: true)]
    private ?string $actionUrl = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    private function __construct()
    {
    }

    public static function fromDomain(PipelineNotification $notification): self
    {
        $record = new self();
        $record->id = $notification->notificationId()->value;
        $record->syncFromDomain($notification);

        return $record;
    }

    public function syncFromDomain(PipelineNotification $notification): void
    {
        $this->sourceId = $notification->sourceId();
        $this->stage = $notification->stage()?->value;
        $this->type = $notification->type();
        $this->title = $notification->title();
        $this->message = $notification->message();
        $this->readFlag = $notification->read();
        $this->actionUrl = $notification->actionUrl();
        $this->createdAt = $notification->createdAt();
    }

    public function toDomain(): PipelineNotification
    {
        $stage = null !== $this->stage ? PipelineStageType::tryFrom($this->stage) : null;

        return PipelineNotification::reconstitute(
            new PipelineNotificationId($this->id),
            $this->sourceId,
            $stage,
            $this->type,
            $this->title,
            $this->message,
            $this->readFlag,
            $this->actionUrl,
            $this->createdAt,
        );
    }
}
